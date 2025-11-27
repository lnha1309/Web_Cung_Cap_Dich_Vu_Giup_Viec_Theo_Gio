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
                    'label' => $address->Nhan,
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
            'label' => ['nullable', 'string', 'max:100'],
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
            'Nhan' => $request->label,
            'DiaChiDayDu' => $request->full_address,
            'is_Deleted' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo địa chỉ thành công.',
            'data' => [
                'id' => $address->ID_DC,
                'unit' => $address->CanHo,
                'label' => $address->Nhan,
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
                'label' => $address->Nhan,
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
            'label' => ['nullable', 'string', 'max:100'],
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
        $address->Nhan = $request->label;
        $address->DiaChiDayDu = $request->full_address;
        $address->ID_Quan = $quan?->ID_Quan;
        $address->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật địa chỉ thành công.',
            'data' => [
                'id' => $address->ID_DC,
                'unit' => $address->CanHo,
                'label' => $address->Nhan,
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
/**
 * Helper function to guess district from address
 * Tối ưu hóa dựa trên format Google Maps
 */
private function guessQuanFromAddress(string $address): ?Quan
{
    $address = trim($address);
    if ($address === '') {
        return null;
    }

    // Normalize helper
    $normalize = function (string $value): string {
        $value = mb_strtolower($value, 'UTF-8');
        // Remove diacritics
        $value = str_replace(
            ['à','á','ả','ã','ạ','ă','ằ','ắ','ẳ','ẵ','ặ','â','ầ','ấ','ẩ','ẫ','ậ',
             'đ',
             'è','é','ẻ','ẽ','ẹ','ê','ề','ế','ể','ễ','ệ',
             'ì','í','ỉ','ĩ','ị',
             'ò','ó','ỏ','õ','ọ','ô','ồ','ố','ổ','ỗ','ộ','ơ','ờ','ớ','ở','ỡ','ợ',
             'ù','ú','ủ','ũ','ụ','ư','ừ','ứ','ử','ữ','ự',
             'ỳ','ý','ỷ','ỹ','ỵ'],
            ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
             'd',
             'e','e','e','e','e','e','e','e','e','e','e',
             'i','i','i','i','i',
             'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
             'u','u','u','u','u','u','u','u','u','u','u',
             'y','y','y','y','y'],
            $value
        );
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    };

    // Tách địa chỉ thành các segments (phân cách bởi dấu phẩy)
    $segments = array_map('trim', explode(',', $address));
    
    // DEBUG - Xóa sau khi fix
    \Log::info('=== ADDRESS MATCHING DEBUG ===');
    \Log::info('Input address: ' . $address);
    \Log::info('Segments: ' . json_encode($segments));
    
    // Lấy tất cả quận
    $quans = Quan::all();
    \Log::info('Total districts in DB: ' . $quans->count());
    
    // Build danh sách patterns để match cho từng quận
    $districtPatterns = [];
    foreach ($quans as $quan) {
        $tenQuan = trim($quan->TenQuan);
        if (!$tenQuan) continue;
        
        $normalizedTenQuan = $normalize($tenQuan);
        $patterns = [];
        
        // Pattern 1: Tên đầy đủ (Quận 7, Huyện Nhà Bè, TP Thủ Đức)
        $patterns[] = [
            'normalized' => $normalizedTenQuan,
            'type' => 'full'
        ];
        
        // Pattern 2: Tên không có prefix (chỉ áp dụng cho tên chữ)
        // Dùng string manipulation thay vì regex để tránh vấn đề Unicode
        $normalizedTenQuanForParsing = $normalize($tenQuan);
        
        // Loại bỏ prefix bằng str_replace
        $withoutPrefix = $normalizedTenQuanForParsing;
        $prefixes = ['quan ', 'huyen ', 'tp ', 'tp. ', 'thanh pho '];
        
        foreach ($prefixes as $prefix) {
            if (strpos($withoutPrefix, $prefix) === 0) {
                $withoutPrefix = substr($withoutPrefix, strlen($prefix));
                $withoutPrefix = trim($withoutPrefix);
                break;
            }
        }
        
        // Nếu đã loại bỏ được prefix và còn lại tên quận
        if ($withoutPrefix !== $normalizedTenQuanForParsing && $withoutPrefix !== '') {
            // Chỉ thêm pattern nếu KHÔNG phải là quận số
            $isQuanSo = preg_match('/^\d+$/', $withoutPrefix);
            
            if (!$isQuanSo) {
                $patterns[] = [
                    'normalized' => $withoutPrefix,
                    'type' => 'name_only'
                ];
            }
        }
        
        // DEBUG - Log patterns for Nhà Bè
        if (strpos($tenQuan, 'Nhà Bè') !== false || strpos($normalizedTenQuan, 'nha be') !== false) {
            \Log::info("District: '{$tenQuan}' (ID: {$quan->ID_Quan})");
            \Log::info("  Normalized full: '{$normalizedTenQuan}'");
            \Log::info("  Patterns: " . json_encode($patterns));
        }
        
        $districtPatterns[] = [
            'quan' => $quan,
            'patterns' => $patterns
        ];
    }
    
    // Thử match theo thứ tự ưu tiên segments
    // Ưu tiên: segment thứ 3 từ cuối (thường là quận), rồi đến các segment khác
    $priorityIndices = [];
    if (count($segments) >= 3) {
        $priorityIndices[] = count($segments) - 3; // Segment thứ 3 từ cuối
    }
    if (count($segments) >= 2) {
        $priorityIndices[] = count($segments) - 2; // Segment thứ 2 từ cuối
    }
    for ($i = 0; $i < count($segments); $i++) {
        if (!in_array($i, $priorityIndices)) {
            $priorityIndices[] = $i;
        }
    }
    
    // Match theo từng segment
    foreach ($priorityIndices as $idx) {
        $segment = trim($segments[$idx]);
        if ($segment === '') continue;
        
        $normalizedSegment = $normalize($segment);
        
        // DEBUG
        \Log::info("Checking segment [$idx]: '$segment' (normalized: '$normalizedSegment')");
        
        // Thử match với từng quận
        foreach ($districtPatterns as $item) {
            foreach ($item['patterns'] as $pattern) {
                $needle = $pattern['normalized'];
                
                // Exact match
                if ($normalizedSegment === $needle) {
                    \Log::info("✓ EXACT MATCH: {$item['quan']->TenQuan}");
                    return $item['quan'];
                }
                
                // Contains match với word boundary
                if (preg_match('/(^|\s)' . preg_quote($needle, '/') . '(\s|$)/u', $normalizedSegment)) {
                    \Log::info("✓ CONTAINS MATCH: {$item['quan']->TenQuan}");
                    return $item['quan'];
                }
            }
        }
    }
    
    // Fallback: tìm trong toàn bộ địa chỉ
    \Log::info('No segment match found, trying full address...');
    $normalizedAddress = $normalize($address);
    \Log::info('Normalized full address: ' . $normalizedAddress);
    
    foreach ($districtPatterns as $item) {
        foreach ($item['patterns'] as $pattern) {
            $needle = $pattern['normalized'];
            if (preg_match('/(^|[\s,])' . preg_quote($needle, '/') . '([\s,]|$)/u', $normalizedAddress)) {
                \Log::info("✓ FALLBACK MATCH: {$item['quan']->TenQuan}");
                return $item['quan'];
            }
        }
    }
    
    \Log::info('✗ NO MATCH FOUND');
    return null;
}
}
