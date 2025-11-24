<?php

namespace App\Http\Controllers;

use App\Models\DonDat;
use App\Models\GoiThang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPackageController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = GoiThang::query();

        if ($search !== '') {
            $query->where('TenGoi', 'like', '%' . $search . '%');
        }

        $packages = $query->orderBy('ID_Goi')->paginate(10)->withQueryString();
        $editingId = $request->old('editing_id');

        return view('admin.packages.index', [
            'packages' => $packages,
            'search' => $search,
            'editingId' => $editingId,
            'isEditing' => !empty($editingId),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $payload = $this->normalizePayload($validated);
        $payload['ID_Goi'] = $this->generatePackageId();

        GoiThang::create($payload);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Đã thêm gói tháng mới.');
    }

    public function update(Request $request, GoiThang $package)
    {
        $validated = $this->validateData($request, $package->ID_Goi);
        $package->update($this->normalizePayload($validated));

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Đã cập nhật gói tháng.');
    }

    public function destroy(GoiThang $package)
    {
        $isInUse = DonDat::where('ID_Goi', $package->ID_Goi)->exists();

        if ($isInUse) {
            return redirect()
                ->route('admin.packages.index')
                ->with('error', 'Không thể xóa gói tháng đang được dùng trong đơn đặt.');
        }

        $package->delete();

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Đã xóa gói tháng.');
    }

    private function validateData(Request $request, ?string $packageId = null): array
    {
        return $request->validate([
            'TenGoi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('GoiThang', 'TenGoi')->ignore($packageId, 'ID_Goi'),
            ],
            'SoNgay' => ['required', 'integer', 'min:1'],
            'PhanTramGiam' => ['required', 'numeric', 'min:0', 'max:100'],
            'Mota' => ['nullable', 'string'],
            'editing_id' => ['nullable', 'string'],
        ]);
    }

    private function normalizePayload(array $data): array
    {
        return [
            'TenGoi' => trim($data['TenGoi']),
            'SoNgay' => $data['SoNgay'],
            'PhanTramGiam' => $data['PhanTramGiam'],
            'Mota' => $data['Mota'] ?? null,
        ];
    }

    private function generatePackageId(): string
    {
        $maxNumber = GoiThang::select('ID_Goi')
            ->where('ID_Goi', 'like', 'GT%')
            ->get()
            ->map(function ($package) {
                return (int) preg_replace('/\D/', '', $package->ID_Goi);
            })
            ->max() ?? 0;

        $next = $maxNumber + 1;

        // Keep length at least 2 digits to align with seed data (GT01)
        $width = $next >= 100 ? 3 : 2;

        return 'GT' . str_pad((string) $next, $width, '0', STR_PAD_LEFT);
    }
}
