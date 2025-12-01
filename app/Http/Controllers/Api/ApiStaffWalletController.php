<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LichSuViNhanVien;
use App\Models\NhanVien;
use App\Services\StaffWalletService;
use App\Services\VNPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiStaffWalletController extends Controller
{
    public function summary(Request $request, StaffWalletService $walletService)
    {
        $staff = $this->requireStaff($request);
        if (!$staff) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi xem duoc vi.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $walletService->summary($staff),
        ]);
    }

    public function history(Request $request)
    {
        $staff = $this->requireStaff($request);
        if (!$staff) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi xem duoc vi.',
            ], 403);
        }

        $page = max((int) $request->query('page', 1), 1);
        $perPage = min(max((int) $request->query('per_page', 50), 1), 200);

        $items = LichSuViNhanVien::where('ID_NV', $staff->ID_NV)
            ->orderByDesc('created_at')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn (LichSuViNhanVien $row) => $this->transform($row));

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function show(Request $request, string $id)
    {
        $staff = $this->requireStaff($request);
        if (!$staff) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi xem duoc vi.',
            ], 403);
        }

        $row = LichSuViNhanVien::where('ID_LSV', $id)
            ->where('ID_NV', $staff->ID_NV)
            ->first();

        if (!$row) {
            return response()->json([
                'success' => false,
                'error' => 'Khong tim thay giao dich.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transform($row),
        ]);
    }

    public function topup(
        Request $request,
        VNPayService $vnPayService,
        StaffWalletService $walletService
    ) {
        $staff = $this->requireStaff($request);
        if (!$staff) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi nap duoc vi.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:10000'],
            'return_url' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $amount = (float) $request->input('amount');
        $currentBalance = $walletService->balance($staff);
        $minRequired = StaffWalletService::MIN_BALANCE;

        if (($currentBalance + $amount) < $minRequired) {
            return response()->json([
                'success' => false,
                'error' => 'So du sau nap phai it nhat 400.000d de mo khoa nhan don.',
            ], 422);
        }

        $reference = 'WALLET_' . strtoupper(Str::random(12));
        $walletService->createPendingTopup($staff, $amount, $reference, [
            'description' => 'Nap tien VNPAY',
            'source' => 'vnpay',
            'type' => 'topup',
        ]);

        $baseReturnUrl = config('vnpay.return_url');
        $returnUrl = $baseReturnUrl;
        if ($request->filled('return_url')) {
            $separator = str_contains($baseReturnUrl, '?') ? '&' : '?';
            $returnUrl = $baseReturnUrl . $separator . 'app_redirect=' . urlencode($request->input('return_url'));
        }

        $paymentUrl = $vnPayService->createPaymentUrl([
            'txn_ref' => $reference,
            'amount' => $amount,
            'order_info' => 'Nap tien vi nhan vien ' . $staff->ID_NV,
            'return_url' => $returnUrl,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_url' => $paymentUrl,
                'reference' => $reference,
            ],
        ]);
    }

    private function transform(LichSuViNhanVien $row): array
    {
        return [
            'id' => $row->ID_LSV,
            'type' => $row->LoaiGiaoDich,
            'transaction_type' => $row->LoaiGiaoDich,
            'direction' => $row->Huong,
            'amount' => (float) $row->SoTien,
            'balance_after' => $row->SoDuSau !== null ? (float) $row->SoDuSau : null,
            'description' => $row->MoTa,
            'note' => $row->MoTa,
            'order_id' => $row->ID_DD,
            'source' => $row->Nguon,
            'reference' => $row->MaThamChieu,
            'transaction_no' => $row->MaGiaoDich,
            'status' => $row->TrangThai,
            'created_at' => $row->created_at?->toDateTimeString(),
        ];
    }

    private function requireStaff(Request $request): ?NhanVien
    {
        $account = $request->user();
        $staff = $account?->nhanVien;

        if (!$account || $account->ID_LoaiTK !== 'staff' || !$staff) {
            return null;
        }

        return $staff;
    }
}
