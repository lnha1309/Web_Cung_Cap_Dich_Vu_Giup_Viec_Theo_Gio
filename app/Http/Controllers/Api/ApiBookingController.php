<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonDat;
use App\Models\ChiTietKhuyenMai;
use App\Models\ChiTietPhuThu;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\LichLamViec;
use App\Models\LichSuThanhToan;
use App\Models\Quan;
use App\Models\DanhGiaNhanVien;
use App\Models\NhanVien;
use App\Models\TaiKhoan;
use App\Support\IdGenerator;
use App\Services\SurchargeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiBookingController extends Controller
{
    /**
     * Get all bookings for authenticated user
     * GET /api/bookings
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

        $bookings = DonDat::where('ID_KH', $khachHang->ID_KH)
            ->orderByDesc('NgayTao')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings->map(function ($booking) {
                $service = DichVu::find($booking->ID_DV);
                $address = DiaChi::find($booking->ID_DC);
                
                return [
                    'id' => $booking->ID_DD,
                    'order_type' => $booking->LoaiDon,
                    'service' => [
                        'id' => $service?->ID_DV,
                        'name' => $service?->TenDV,
                    ],
                    'address' => $address ? [
                        'unit' => $address->CanHo,
                        'full_address' => $address->DiaChiDayDu,
                    ] : null,
                    'note' => $booking->GhiChu,
                    'work_date' => $booking->NgayLam,
                    'start_time' => $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null,
                    'duration_hours' => (float) $booking->ThoiLuongGio,
                    'status' => $booking->TrangThaiDon,
                    'total_amount' => (float) $booking->TongTien,
                    'discounted_amount' => (float) $booking->TongTienSauGiam,
                    'staff_id' => $booking->ID_NV,
                    'created_at' => $booking->NgayTao,
                ];
            })
        ]);
    }

    /**
     * Get single booking
     * GET /api/bookings/{id}
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

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn đặt.'
            ], 404);
        }

        $service = DichVu::find($booking->ID_DV);
        $address = DiaChi::find($booking->ID_DC);
        
        // Get applied vouchers
        $vouchers = ChiTietKhuyenMai::where('ID_DD', $id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->ID_DD,
                'order_type' => $booking->LoaiDon,
                'service' => [
                    'id' => $service?->ID_DV,
                    'name' => $service?->TenDV,
                    'price' => $service ? (float) $service->GiaDV : 0,
                ],
                'address' => $address ? [
                    'id' => $address->ID_DC,
                    'unit' => $address->CanHo,
                    'full_address' => $address->DiaChiDayDu,
                ] : null,
                'note' => $booking->GhiChu,
                'work_date' => $booking->NgayLam,
                'start_time' => $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null,
                'duration_hours' => (float) $booking->ThoiLuongGio,
                'status' => $booking->TrangThaiDon,
                'total_amount' => (float) $booking->TongTien,
                'discounted_amount' => (float) $booking->TongTienSauGiam,
                'staff_id' => $booking->ID_NV,
                'created_at' => $booking->NgayTao,
                'vouchers' => $vouchers->map(function ($v) {
                    return [
                        'voucher_code' => $v->ID_KM,
                        'discount_amount' => (float) $v->TienGiam,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Create new booking
     * POST /api/bookings
     */
    public function store(Request $request, SurchargeService $surchargeService)
    {
        $validator = \Validator::make($request->all(), [
            'order_type' => ['required', 'in:hour,month'],
            'service_id' => ['required', 'string'],
            'address_id' => ['nullable', 'string'],
            'address_text' => ['nullable', 'string'],
            'address_unit' => ['nullable', 'string'],
            'work_date' => ['nullable', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'discounted_amount' => ['nullable', 'numeric', 'min:0'],
            'staff_id' => ['nullable', 'string'],
            'vouchers' => ['nullable', 'array'],
            'vouchers.*.code' => ['required', 'string'],
            'vouchers.*.discount_amount' => ['nullable', 'numeric'],
            'has_pets' => ['nullable', 'boolean'],
            'repeat_days' => ['nullable', 'array'],
            'repeat_days.*' => ['integer', 'between:0,6'],
            'note' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'in:cash,vnpay'],
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

        $paymentMethod = $request->payment_method ?? 'cash';

        // Check vouchers reuse
        if ($request->has('vouchers') && is_array($request->vouchers)) {
            foreach ($request->vouchers as $voucher) {
                $code = $voucher['code'] ?? null;
                if ($code && $this->voucherUsedByCustomer($khachHang->ID_KH, $code)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Mã khuyến mãi ' . $code . ' bạn đã sử dụng trước đó nên không thể áp lại.'
                    ], 422);
                }
            }
        }

        // Handle address
        $idDc = $request->address_id;
        if (!$idDc && $request->address_text) {
            $addressText = trim($request->address_text);
            $addressUnit = $request->address_unit ? trim($request->address_unit) : null;

            // Try to find existing address
            $query = $khachHang->diaChis()
                ->where('is_Deleted', false)
                ->where('DiaChiDayDu', $addressText);
            if ($addressUnit) {
                $query->where('CanHo', $addressUnit);
            }

            $existingAddress = $query->first();

            if ($existingAddress) {
                $idDc = $existingAddress->ID_DC;
            } else {
                // Create new address
                $quan = $this->guessQuanFromAddress($addressText);
                $newIdDc = IdGenerator::next('DiaChi', 'ID_DC', 'DC_');

                DiaChi::create([
                    'ID_DC' => $newIdDc,
                    'ID_KH' => $khachHang->ID_KH,
                    'ID_Quan' => $quan?->ID_Quan,
                    'CanHo' => $addressUnit,
                    'DiaChiDayDu' => $addressText,
                ]);

                $idDc = $newIdDc;
            }
        }

        // Create booking
        $prefix = $request->order_type === 'month' ? 'DD_month_' : 'DD_hour_';
        $idDon = IdGenerator::next('DonDat', 'ID_DD', $prefix);

        $tongTien = (float) $request->total_amount;
        $tongSauGiam = $request->has('discounted_amount') && $request->discounted_amount !== null
            ? (float) $request->discounted_amount
            : $tongTien;
        $gioBatDau = $request->start_time ? $request->start_time . ':00' : null;

        $hasPets = $request->has('has_pets') ? (bool) $request->has_pets : false;
        $repeatDays = is_array($request->repeat_days)
            ? array_map('intval', $request->repeat_days)
            : [];
        $ngayLam = $request->order_type === 'hour' ? $request->work_date : null;

        $surchargeResult = $surchargeService->calculate(
            $request->order_type,
            $ngayLam,
            $gioBatDau,
            $request->order_type === 'month' ? $repeatDays : [],
            $hasPets
        );

        $tongTien += $surchargeResult['total'];
        $tongSauGiam += $surchargeResult['total'];

        $trangThaiDon = $request->staff_id ? 'assigned' : 'finding_staff';

        $booking = DonDat::create([
            'ID_DD' => $idDon,
            'LoaiDon' => $request->order_type,
            'ID_DV' => $request->service_id,
            'ID_KH' => $khachHang->ID_KH,
            'ID_DC' => $idDc,
            'GhiChu' => $request->note,
            'NgayLam' => $request->work_date,
            'GioBatDau' => $gioBatDau,
            'ThoiLuongGio' => $request->duration_hours,
            'ID_Goi' => null,
            'NgayBatDauGoi' => null,
            'NgayKetThucGoi' => null,
            'TrangThaiDon' => $trangThaiDon,
            'TongTien' => $tongTien,
            'TongTienSauGiam' => $tongSauGiam,
            'ID_NV' => $request->staff_id,
        ]);

        if ($booking->TrangThaiDon === 'assigned' && $booking->ID_NV) {
            $this->notifyStaffAssigned($booking);
        }

        // Save surcharges
        foreach ($surchargeResult['items'] as $item) {
            ChiTietPhuThu::create([
                'ID_PT'  => $item['id'],
                'ID_DD'  => $idDon,
                'Ghichu' => $item['note'] . ' - ' . number_format($item['unit_amount']) . ' x ' . $item['quantity'],
            ]);
        }

        // Save vouchers
        if ($request->has('vouchers') && is_array($request->vouchers)) {
            foreach ($request->vouchers as $voucher) {
                $code = $voucher['code'] ?? null;
                $amount = $voucher['discount_amount'] ?? 0;
                
                if ($code) {
                    ChiTietKhuyenMai::create([
                        'ID_DD' => $idDon,
                        'ID_KM' => $code,
                        'TienGiam' => (float) $amount,
                    ]);
                }
            }
        }

        // Create payment record
        LichSuThanhToan::create([
            'ID_LSTT' => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
            'PhuongThucThanhToan' => $paymentMethod === 'vnpay' ? 'VNPay' : 'TienMat',
            'TrangThai' => $paymentMethod === 'cash' ? 'ThanhCong' : 'ChoXuLy',
            'SoTienThanhToan' => $tongSauGiam,
            'MaGiaoDichVNPAY' => null,
            'ID_DD' => $idDon,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn đặt thành công.',
            'data' => [
                'booking_id' => $idDon,
                'status' => $trangThaiDon,
            ]
        ], 201);
    }

    /**
     * Find available staff
     * POST /api/bookings/find-staff
     */
    public function findStaff(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'work_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'address' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ngayLam = $request->work_date;
        $gioBatDau = $request->start_time;
        $thoiLuong = (int) $request->duration_hours;

        $start = Carbon::createFromFormat('H:i', $gioBatDau);
        $gioKetThuc = $start->copy()->addHours($thoiLuong)->format('H:i:s');
        $gioBatDauSql = $start->format('H:i:s');

        $lich = LichLamViec::with('nhanVien')
            ->where('NgayLam', $ngayLam)
            ->where('TrangThai', 'ready')
            ->where('GioBatDau', '<=', $gioBatDauSql)
            ->where('GioKetThuc', '>=', $gioKetThuc)
            ->get();

        $customerQuan = null;
        $diaChiText = $request->address ?? '';
        if ($diaChiText !== '') {
            $customerQuan = $this->guessQuanFromAddress($diaChiText);
        }

        $results = [];

        foreach ($lich as $item) {
            $nv = $item->nhanVien;
            if (!$nv) {
                continue;
            }

            $avgScore = DanhGiaNhanVien::where('ID_NV', $nv->ID_NV)->avg('Diem');
            $ratingPercent = $avgScore ? round(((float) $avgScore) / 5 * 100) : 30;

            $proximityPercent = 50;
            if ($customerQuan) {
                if ($nv->ID_Quan === $customerQuan->ID_Quan) {
                    $proximityPercent = 100;
                } elseif (
                    $nv->KhuVucLamViec &&
                    mb_stripos($nv->KhuVucLamViec, $customerQuan->TenQuan) !== false
                ) {
                    $proximityPercent = 80;
                } else {
                    $nvQuan = $nv->ID_Quan ? Quan::find($nv->ID_Quan) : null;
                    if (
                        $nvQuan &&
                        $nvQuan->ViDo !== null &&
                        $nvQuan->KinhDo !== null &&
                        $customerQuan->ViDo !== null &&
                        $customerQuan->KinhDo !== null
                    ) {
                        $distKm = $this->distanceKm(
                            (float) $nvQuan->ViDo,
                            (float) $nvQuan->KinhDo,
                            (float) $customerQuan->ViDo,
                            (float) $customerQuan->KinhDo
                        );
                        $proximityPercent = max(0, 100 - (int) round($distKm * 10));
                    }
                }
            }

            $score = $ratingPercent * 0.3 + $proximityPercent * 0.7;

            $jobsCompleted = DonDat::where('ID_NV', $nv->ID_NV)
                ->where('TrangThaiDon', 'done')
                ->count();

            $results[] = [
                'id' => $nv->ID_NV,
                'name' => $nv->Ten_NV,
                'avatar' => $nv->HinhAnh,
                'phone' => $nv->SDT,
                'rating_percent' => $ratingPercent,
                'proximity_percent' => $proximityPercent,
                'score' => (float) $score,
                'jobs_completed' => $jobsCompleted,
            ];
        }

        usort($results, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Calculate quote
     * POST /api/bookings/quote
     */
    public function calculateQuote(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'duration' => ['required', 'integer', 'in:2,3,4'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $duration = (int) $request->duration;

        $idDv = match ($duration) {
            2 => 'DV001',
            3 => 'DV002',
            4 => 'DV003',
            default => null,
        };

        $service = DichVu::find($idDv);

        if (!$service) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy dịch vụ.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_id' => $service->ID_DV,
                'service_name' => $service->TenDV,
                'price' => (float) $service->GiaDV,
                'duration_hours' => (float) $service->ThoiLuong,
            ]
        ]);
    }

    /**
     * Cancel booking
     * PUT /api/bookings/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;

        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 404);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn đặt.'
            ], 404);
        }

        if (in_array($booking->TrangThaiDon, ['done', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'error' => 'Không thể hủy đơn đặt này.'
            ], 422);
        }

        $booking->TrangThaiDon = 'cancelled';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Hủy đơn đặt thành công.'
        ]);
    }

    private function notifyStaffAssigned(DonDat $booking): void
    {
        try {
            $nhanVien = NhanVien::where('ID_NV', $booking->ID_NV)->first();
            if (!$nhanVien || !$nhanVien->ID_TK) {
                return;
            }

            $account = TaiKhoan::where('ID_TK', $nhanVien->ID_TK)->first();
            if (!$account || !$account->onesignal_player_id) {
                return;
            }

            $service = DichVu::find($booking->ID_DV);
            $title = 'Đơn mới được gán';
            $body = 'Đơn ' . $booking->ID_DD . ' - Dịch vụ ' . ($service?->TenDV ?? '');
            $this->sendOneSignalToUser($account, $title, $body);
        } catch (\Exception $e) {
            // Ignore notification errors to avoid breaking main flow
        }
    }

    private function sendOneSignalToUser(TaiKhoan $account, string $title, string $body): void
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.api_key');
        if (!$appId || !$apiKey || !$account->onesignal_player_id) {
            return;
        }

        $payload = [
            'app_id' => $appId,
            'include_player_ids' => [$account->onesignal_player_id],
            'headings' => ['en' => $title],
            'contents' => ['en' => $body],
        ];

        Http::withHeaders([
            'Authorization' => 'Basic ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.onesignal.com/notifications', $payload);
    }

    // Helper methods
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

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

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
