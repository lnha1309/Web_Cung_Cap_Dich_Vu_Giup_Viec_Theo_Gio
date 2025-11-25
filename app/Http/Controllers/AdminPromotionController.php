<?php

namespace App\Http\Controllers;

use App\Models\KhuyenMai;
use App\Models\ChiTietKhuyenMai;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class AdminPromotionController extends Controller
{
    public function index(Request $request)
    {
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

        $payload = [
            'ID_KM' => $this->generatePromotionId(),
            'Ten_KM' => trim($validated['Ten_KM']),
            'MoTa' => $validated['MoTa'] ?? null,
            'PhanTramGiam' => $validated['PhanTramGiam'],
            'GiamToiDa' => $validated['GiamToiDa'] ?? 0,
            'TrangThai' => $this->resolveStatus($request, 'activated'),
        ];

        KhuyenMai::create($payload);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã thêm khuyến mãi.');
    }

    public function update(Request $request, KhuyenMai $promotion)
    {
        $validated = $this->validateData($request, $promotion->ID_KM);

        $payload = [
            'Ten_KM' => trim($validated['Ten_KM']),
            'MoTa' => $validated['MoTa'] ?? null,
            'PhanTramGiam' => $validated['PhanTramGiam'],
            'GiamToiDa' => $validated['GiamToiDa'] ?? 0,
            'TrangThai' => $this->resolveStatus($request, $promotion->TrangThai),
        ];

        $promotion->update($payload);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã cập nhật khuyến mãi.');
    }

    public function destroy(KhuyenMai $promotion)
    {
        $inUse = ChiTietKhuyenMai::where('ID_KM', $promotion->ID_KM)->exists();

        if ($inUse) {
            return redirect()
                ->route('admin.promotions.index')
                ->with('error', 'Không thể xoá khuyến mãi đã được áp dụng.');
        }

        $promotion->delete();

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã xoá khuyến mãi.');
    }

    public function toggle(Request $request, KhuyenMai $promotion)
    {
        $request->validate([
            'force_status' => ['required', Rule::in(['activated', 'deactivated'])],
        ]);

        $promotion->update(['TrangThai' => $request->input('force_status')]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã cập nhật trạng thái khuyến mãi.');
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
            'TrangThai' => ['nullable', Rule::in(['activated', 'deactivated'])],
            'editing_id' => ['nullable', 'string'],
        ]);
    }

    private function resolveStatus(Request $request, string $fallback): string
    {
        if ($request->filled('force_status')) {
            return $request->input('force_status') === 'activated' ? 'activated' : 'deactivated';
        }

        if ($request->filled('expiry_date')) {
            try {
                $expiry = Carbon::parse($request->input('expiry_date'))->endOfDay();
                return now()->lessThanOrEqualTo($expiry) ? 'activated' : 'deactivated';
            } catch (\Exception $e) {
                // ignore parse errors, fall back
            }
        }

        if ($request->filled('TrangThai') && in_array($request->input('TrangThai'), ['activated', 'deactivated'])) {
            return $request->input('TrangThai');
        }

        return $fallback;
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
}
