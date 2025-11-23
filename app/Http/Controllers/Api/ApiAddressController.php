<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiaChi;
use App\Models\Quan;
use App\Support\IdGenerator;
use Illuminate\Http\Request;

class ApiAddressController extends Controller
{
    /**
     * Get all addresses for authenticated user
     * GET /api/addresses
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

        $addresses = $khachHang->diaChis()
            ->where('is_Deleted', false)
            ->orderBy('ID_DC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses->map(function ($address) {
                $quan = Quan::find($address->ID_Quan);
                return [
                    'id' => $address->ID_DC,
                    'unit' => $address->CanHo,
                    'full_address' => $address->DiaChiDayDu,
                    'district_id' => $address->ID_Quan,
                    'district_name' => $quan ? $quan->TenQuan : null,
                ];
            })
        ]);
    }

    /**
     * Create new address
     * POST /api/addresses
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'unit' => ['nullable', 'string', 'max:255'],
            'full_address' => ['required', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        // Guess district from address
        $quan = $this->guessQuanFromAddress($request->full_address);

        $idDc = IdGenerator::next('DiaChi', 'ID_DC', 'DC_');

        $address = DiaChi::create([
            'ID_DC' => $idDc,
            'ID_KH' => $khachHang->ID_KH,
            'ID_Quan' => $quan?->ID_Quan,
            'CanHo' => $request->unit,
            'DiaChiDayDu' => $request->full_address,
            'is_Deleted' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo địa chỉ thành công.',
            'data' => [
                'id' => $address->ID_DC,
                'unit' => $address->CanHo,
                'full_address' => $address->DiaChiDayDu,
                'district_id' => $address->ID_Quan,
                'district_name' => $quan ? $quan->TenQuan : null,
            ]
        ], 201);
    }

    /**
     * Get single address
     * GET /api/addresses/{id}
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        $address = DiaChi::where('ID_DC', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->where('is_Deleted', false)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy địa chỉ.'
            ], 404);
        }

        $quan = Quan::find($address->ID_Quan);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $address->ID_DC,
                'unit' => $address->CanHo,
                'full_address' => $address->DiaChiDayDu,
                'district_id' => $address->ID_Quan,
                'district_name' => $quan ? $quan->TenQuan : null,
            ]
        ]);
    }

    /**
     * Update address
     * PUT /api/addresses/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'unit' => ['nullable', 'string', 'max:255'],
            'full_address' => ['required', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        $address = DiaChi::where('ID_DC', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->where('is_Deleted', false)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy địa chỉ.'
            ], 404);
        }

        // Update district if address changed
        $quan = $this->guessQuanFromAddress($request->full_address);

        $address->CanHo = $request->unit;
        $address->DiaChiDayDu = $request->full_address;
        $address->ID_Quan = $quan?->ID_Quan;
        $address->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật địa chỉ thành công.',
            'data' => [
                'id' => $address->ID_DC,
                'unit' => $address->CanHo,
                'full_address' => $address->DiaChiDayDu,
                'district_id' => $address->ID_Quan,
                'district_name' => $quan ? $quan->TenQuan : null,
            ]
        ]);
    }

    /**
     * Delete address (soft delete)
     * DELETE /api/addresses/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        $address = DiaChi::where('ID_DC', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->where('is_Deleted', false)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy địa chỉ.'
            ], 404);
        }

        $address->is_Deleted = true;
        $address->save();

        return response()->json([
            'success' => true,
            'message' => 'Xóa địa chỉ thành công.'
        ]);
    }

    /**
     * Helper function to guess district from address
     */
    private function guessQuanFromAddress(string $address): ?Quan
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $segments = array_map('trim', explode(',', $address));
        $candidate = null;

        if (count($segments) >= 3) {
            $candidate = $segments[count($segments) - 2];
        } elseif (count($segments) >= 2) {
            $candidate = $segments[1];
        } else {
            $candidate = $address;
        }

        $candidate = trim((string) $candidate);
        if ($candidate === '') {
            return null;
        }

        $quan = Quan::where('TenQuan', 'like', '%' . $candidate . '%')->first();
        if ($quan) {
            return $quan;
        }

        $normalize = static function (string $value): string {
            $value = preg_replace('/^(Quan|Huyen|TP\.?|Thanh pho)\s+/iu', '', $value);
            return trim((string) $value);
        };

        $normalizedCandidate = $normalize($candidate);
        if ($normalizedCandidate === '') {
            return null;
        }

        $quans = Quan::all();

        foreach ($quans as $quanItem) {
            if (!$quanItem->TenQuan) {
                continue;
            }

            $normalizedTenQuan = $normalize($quanItem->TenQuan);
            if ($normalizedTenQuan !== '' &&
                mb_stripos($normalizedCandidate, $normalizedTenQuan) !== false) {
                return $quanItem;
            }

            if (mb_stripos($address, $quanItem->TenQuan) !== false) {
                return $quanItem;
            }
        }

        return null;
    }
}
