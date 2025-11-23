<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KhuyenMai;
use App\Models\DonDat;
use App\Models\ChiTietKhuyenMai;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiVoucherController extends Controller
{
    /**
     * Get all available vouchers
     * GET /api/vouchers
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        $today = Carbon::today();
        
        $vouchers = KhuyenMai::where('TrangThai', 'activated')
            ->where(function ($query) use ($today) {
                $query->whereNull('NgayBatDau')
                    ->orWhere('NgayBatDau', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('NgayKetThuc')
                    ->orWhere('NgayKetThuc', '>=', $today);
            })
            ->get();

        $result = $vouchers->map(function ($voucher) use ($khachHang) {
            // Check if customer already used this voucher
            $alreadyUsed = $this->voucherUsedByCustomer($khachHang->ID_KH, $voucher->ID_KM);

            return [
                'code' => $voucher->ID_KM,
                'name' => $voucher->Ten_KM,
                'description' => $voucher->MoTa,
                'discount_percent' => (float) $voucher->PhanTramGiam,
                'max_discount' => $voucher->GiamToiDa ? (float) $voucher->GiamToiDa : null,
                'start_date' => $voucher->NgayBatDau,
                'end_date' => $voucher->NgayKetThuc,
                'already_used' => $alreadyUsed,
                'can_use' => !$alreadyUsed,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Apply voucher and calculate discount
     * POST /api/vouchers/apply
     */
    public function apply(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'code' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $code = $request->code;
        $amount = (float) $request->amount;

        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Vui lòng đăng nhập trước khi áp mã khuyến mãi.'
            ], 403);
        }

        $km = KhuyenMai::where('ID_KM', $code)
            ->where('TrangThai', 'activated')
            ->first();

        if (!$km) {
            return response()->json([
                'success' => false,
                'error' => 'Mã khuyến mãi không hợp lệ hoặc đã ngưng hoạt động.'
            ], 422);
        }

        $today = Carbon::today();
        if (($km->NgayBatDau && $today->lt(Carbon::parse($km->NgayBatDau))) ||
            ($km->NgayKetThuc && $today->gt(Carbon::parse($km->NgayKetThuc)))) {
            return response()->json([
                'success' => false,
                'error' => 'Mã khuyến mãi đã hết hạn.'
            ], 422);
        }

        // Check if customer already used this voucher
        if ($this->voucherUsedByCustomer($khachHang->ID_KH, $code)) {
            return response()->json([
                'success' => false,
                'error' => 'Mã khuyến mãi này bạn đã sử dụng trước đó nên không thể áp lại.'
            ], 422);
        }

        $discount = $amount * ((float) $km->PhanTramGiam / 100);
        if ($km->GiamToiDa !== null) {
            $discount = min($discount, (float) $km->GiamToiDa);
        }

        $final = max(0, $amount - $discount);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $km->ID_KM,
                'name' => $km->Ten_KM,
                'discount_percent' => (float) $km->PhanTramGiam,
                'discount_amount' => (float) $discount,
                'final_amount' => (float) $final,
                'original_amount' => (float) $amount,
            ]
        ]);
    }

    /**
     * Get user's voucher history
     * GET /api/vouchers/history
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        // Get all bookings with vouchers
        $usedVouchers = ChiTietKhuyenMai::whereHas('donDat', function ($query) use ($khachHang) {
            $query->where('ID_KH', $khachHang->ID_KH);
        })
        ->with(['khuyenMai', 'donDat'])
        ->orderByDesc('ID_DD')
        ->get();

        $result = $usedVouchers->map(function ($detail) {
            $voucher = $detail->khuyenMai;
            $booking = $detail->donDat;

            return [
                'booking_id' => $detail->ID_DD,
                'voucher_code' => $detail->ID_KM,
                'voucher_name' => $voucher ? $voucher->Ten_KM : null,
                'discount_amount' => (float) $detail->TienGiam,
                'used_at' => $booking ? $booking->NgayTao : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Helper method to check if customer used voucher
     */
    private function voucherUsedByCustomer(string $customerId, string $code): bool
    {
        return DonDat::where('ID_KH', $customerId)
            ->whereExists(function ($sub) use ($code) {
                $sub->selectRaw('1')
                    ->from('ChiTietKhuyenMai')
                    ->whereColumn('ChiTietKhuyenMai.ID_DD', 'DonDat.ID_DD')
                    ->where('ChiTietKhuyenMai.ID_KM', $code);
            })
            ->exists();
    }
}
