<?php

namespace App\Http\Controllers;

use App\Models\ChiTietPhuThu;
use App\Models\PhuThu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSurchargeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = PhuThu::query();

        if ($search !== '') {
            $query->where('Ten_PT', 'like', '%' . $search . '%');
        }

        $surcharges = $query->orderBy('ID_PT')->paginate(10)->withQueryString();
        $editingId = $request->old('editing_id');

        return view('admin.surcharges.index', [
            'surcharges' => $surcharges,
            'search' => $search,
            'editingId' => $editingId,
            'isEditing' => !empty($editingId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $payload = $this->normalizePayload($validated);
        $payload['ID_PT'] = $this->generateSurchargeId();

        PhuThu::create($payload);

        return redirect()
            ->route('admin.surcharges.index')
            ->with('success', 'Đã thêm phụ thu mới.');
    }

    public function update(Request $request, PhuThu $surcharge)
    {
        $validated = $this->validateData($request, $surcharge->ID_PT);
        $surcharge->update($this->normalizePayload($validated));

        return redirect()
            ->route('admin.surcharges.index')
            ->with('success', 'Đã cập nhật phụ thu.');
    }

    public function destroy(PhuThu $surcharge)
    {
        $inUse = ChiTietPhuThu::where('ID_PT', $surcharge->ID_PT)->exists();

        if ($inUse) {
            return redirect()
                ->route('admin.surcharges.index')
                ->with('error', 'Không thể xóa phụ thu đã được dùng trong chi tiết phụ thu.');
        }

        $surcharge->delete();

        return redirect()
            ->route('admin.surcharges.index')
            ->with('success', 'Đã xóa phụ thu.');
    }

    private function validateData(Request $request, ?string $surchargeId = null): array
    {
        return $request->validate([
            'Ten_PT' => [
                'required',
                'string',
                'max:255',
                Rule::unique('PhuThu', 'Ten_PT')->ignore($surchargeId, 'ID_PT'),
            ],
            'GiaCuoc' => ['required', 'numeric', 'min:0'],
            'editing_id' => ['nullable', 'string'],
        ]);
    }

    private function normalizePayload(array $data): array
    {
        return [
            'Ten_PT' => trim($data['Ten_PT']),
            'GiaCuoc' => $data['GiaCuoc'],
        ];
    }

    private function generateSurchargeId(): string
    {
        $maxNumber = PhuThu::select('ID_PT')
            ->where('ID_PT', 'like', 'PT%')
            ->get()
            ->map(function ($item) {
                return (int) preg_replace('/\D/', '', $item->ID_PT);
            })
            ->max() ?? 0;

        $next = $maxNumber + 1;
        $width = $next >= 100 ? 3 : 3; // keep PT001 style

        return 'PT' . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
    }
}
