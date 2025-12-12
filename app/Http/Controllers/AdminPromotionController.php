<?php

namespace App\Http\Controllers;

use App\Models\KhuyenMai;
use App\Models\ChiTietKhuyenMai;
use App\Models\KhachHang;
use App\Mail\PromotionNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class AdminPromotionController extends Controller
{
    public function index(Request $request)
    {
        // Auto-update status based on expiry date
        KhuyenMai::where('TrangThai', 'activated')
            ->whereNotNull('NgayHetHan')
            ->where('NgayHetHan', '<', Carbon::now()->startOfDay())
            ->update(['TrangThai' => 'deactivated']);

        $status = $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $query = KhuyenMai::query();

        if ($status !== '') {
            $query->where('TrangThai', $status);
        }

        if ($search !== '') {
            $query->where('Ten_KM', 'like', '%' . $search . '%');
        }

        $promotions = $query->orderBy('ID_KM')->paginate(10)->withQueryString();
        $editingId = $request->old('editing_id');

        return view('admin.promotions.index', [
            'promotions' => $promotions,
            'search' => $search,
            'status' => $status,
            'editingId' => $editingId,
            'isEditing' => !empty($editingId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        $status = 'activated';
        if (!empty($validated['expiry_date'])) {
            $expiry = Carbon::parse($validated['expiry_date'])->endOfDay();
            if (now()->greaterThan($expiry)) {
                $status = 'deactivated';
            }
        }

        $payload = [
            'ID_KM' => $this->generatePromotionId(),
            'Ten_KM' => trim($validated['Ten_KM']),
            'MoTa' => $validated['MoTa'] ?? null,
            'PhanTramGiam' => $validated['PhanTramGiam'],
            'GiamToiDa' => $validated['GiamToiDa'] ?? 0,
            'TrangThai' => $status,
            'NgayHetHan' => $validated['expiry_date'] ?? null,
        ];

        $promotion = KhuyenMai::create($payload);

        if ($request->filled('notification_target')) {
            $this->notifyCustomers($promotion, $request->input('notification_target'), $request->input('notification_note'));
        }

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã thêm khuyến mãi.');
    }

    public function update(Request $request, KhuyenMai $promotion)
    {
        $validated = $this->validateData($request, $promotion->ID_KM);

        $status = 'activated';
        if (!empty($validated['expiry_date'])) {
            $expiry = Carbon::parse($validated['expiry_date'])->endOfDay();
            if (now()->greaterThan($expiry)) {
                $status = 'deactivated';
            }
        }

        $payload = [
            'Ten_KM' => trim($validated['Ten_KM']),
            'MoTa' => $validated['MoTa'] ?? null,
            'PhanTramGiam' => $validated['PhanTramGiam'],
            'GiamToiDa' => $validated['GiamToiDa'] ?? 0,
            'TrangThai' => $status,
            'NgayHetHan' => $validated['expiry_date'] ?? null,
        ];

        $promotion->update($payload);

        if ($request->filled('notification_target')) {
            $this->notifyCustomers($promotion, $request->input('notification_target'), $request->input('notification_note'));
        }

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã cập nhật khuyến mãi.');
    }

    public function destroy(KhuyenMai $promotion)
    {
        // Soft delete: chỉ đánh dấu is_delete = true
        $promotion->update(['is_delete' => true]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã xoá khuyến mãi.');
    }

    public function restore(KhuyenMai $promotion)
    {
        $promotion->update(['is_delete' => false]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã khôi phục khuyến mãi.');
    }

    private function validateData(Request $request, ?string $id = null): array
    {
        return $request->validate([
            'Ten_KM' => [
                'required',
                'string',
                'max:255',
                Rule::unique('KhuyenMai', 'Ten_KM')->ignore($id, 'ID_KM'),
            ],
            'MoTa' => ['nullable', 'string'],
            'PhanTramGiam' => ['required', 'numeric', 'min:0', 'max:100'],
            'GiamToiDa' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'editing_id' => ['nullable', 'string'],
            'notification_target' => ['nullable', Rule::in(['all', 'loyal', 'new'])],
            'notification_note' => ['nullable', 'string'],
        ]);
    }

    private function generatePromotionId(): string
    {
        $maxNumber = KhuyenMai::select('ID_KM')
            ->where('ID_KM', 'like', 'KM%')
            ->get()
            ->map(function ($item) {
                return (int) preg_replace('/\D/', '', $item->ID_KM);
            })
            ->max() ?? 0;

        $next = $maxNumber + 1;

        return 'KM' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function notifyCustomers(KhuyenMai $promotion, string $target, ?string $note)
    {
        if (!$target) return;

        $query = KhachHang::query()->with('donDats');

        switch ($target) {
            case 'loyal':
                // Customers with total spend > 1,000,000
                $query->whereHas('donDats', function ($q) {
                    $q->selectRaw('sum(TongTienSauGiam) as total_spend')
                      ->groupBy('ID_KH')
                      ->havingRaw('total_spend > 1000000');
                });
                break;
            case 'new':
                // Customers with 0 orders
                $query->doesntHave('donDats');
                break;
            case 'all':
            default:
                // All customers
                break;
        }

        $customers = $query->get();

        foreach ($customers as $customer) {
            if ($customer->Email) {
                Mail::to($customer->Email)->send(new PromotionNotification($promotion, $note));
            }
        }
    }
}
