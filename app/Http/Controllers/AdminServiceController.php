<?php

namespace App\Http\Controllers;

use App\Models\DichVu;
use App\Models\DonDat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', '');

        $sortOptions = [
            'price_asc' => ['GiaDV', 'asc'],
            'price_desc' => ['GiaDV', 'desc'],
            'duration_asc' => ['ThoiLuong', 'asc'],
            'duration_desc' => ['ThoiLuong', 'desc'],
        ];

        $query = DichVu::query();

        if ($search !== '') {
            $query->where('TenDV', 'like', '%' . $search . '%');
        }

        if (isset($sortOptions[$sort])) {
            [$column, $direction] = $sortOptions[$sort];
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('ID_DV');
        }

        $services = $query->paginate(10)->withQueryString();

        $editingId = $request->old('editing_id');

        return view('admin.services.index', [
            'services' => $services,
            'search' => $search,
            'sort' => $sort,
            'editingId' => $editingId,
            'isEditing' => !empty($editingId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $payload = $this->normalizePayload($validated);
        $payload['ID_DV'] = $this->generateServiceId();

        DichVu::create($payload);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Đã thêm dịch vụ mới.');
    }

    public function update(Request $request, DichVu $service)
    {
        $validated = $this->validateData($request, $service->ID_DV);

        $service->update($this->normalizePayload($validated));

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Đã cập nhật dịch vụ.');
    }

    public function destroy(DichVu $service)
    {
        // Soft delete: chỉ đánh dấu is_delete = true
        $service->update(['is_delete' => true]);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Đã xóa dịch vụ.');
    }

    public function restore(DichVu $service)
    {
        $service->update(['is_delete' => false]);

        return redirect()
            ->route('admin.services.index')
            ->with('success', 'Đã khôi phục dịch vụ.');
    }

    private function validateData(Request $request, ?string $serviceId = null): array
    {
        return $request->validate([
            'TenDV' => [
                'required',
                'string',
                'max:255',
                Rule::unique('DichVu', 'TenDV')->ignore($serviceId, 'ID_DV'),
            ],
            'MoTa' => ['nullable', 'string'],
            'GiaDV' => ['required', 'numeric', 'min:0.01'],
            'DienTichToiDa' => ['nullable', 'numeric', 'min:0'],
            'SoPhong' => ['nullable', 'integer', 'min:0'],
            'ThoiLuong' => ['required', 'numeric', 'min:0.01'],
            'editing_id' => ['nullable', 'string'],
        ]);
    }

    private function normalizePayload(array $data): array
    {
        return [
            'TenDV' => trim($data['TenDV']),
            'MoTa' => $data['MoTa'] ?? null,
            'GiaDV' => $data['GiaDV'],
            'DienTichToiDa' => $data['DienTichToiDa'] ?? null,
            'SoPhong' => $data['SoPhong'] ?? null,
            'ThoiLuong' => $data['ThoiLuong'],
        ];
    }

    private function generateServiceId(): string
    {
        $maxNumber = DichVu::select('ID_DV')
            ->where('ID_DV', 'like', 'DV%')
            ->get()
            ->map(function ($service) {
                return (int) preg_replace('/\D/', '', $service->ID_DV);
            })
            ->max() ?? 0;

        $next = $maxNumber + 1;

        return 'DV' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
