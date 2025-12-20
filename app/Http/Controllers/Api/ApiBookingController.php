<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DonDat;
use App\Models\ChiTietKhuyenMai;
use App\Models\ChiTietPhuThu;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\GoiThang;
use App\Models\LichBuoiThang;
use App\Models\LichLamViec;
use App\Models\LichSuThanhToan;
use App\Models\LichTheoTuan;
use App\Models\Quan;
use App\Models\DanhGiaNhanVien;
use App\Models\NhanVien;
use App\Models\TaiKhoan;
use App\Support\IdGenerator;
use App\Services\SurchargeService;
use App\Services\VNPayService;
use App\Services\RefundService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $staff = $booking->ID_NV ? NhanVien::find($booking->ID_NV) : null;
        // Chỉ xét giao dịch thanh toán (bỏ qua giao dịch hoàn tiền) để tránh hiển thị trạng thái chờ sai
        $paymentRecord = LichSuThanhToan::where('ID_DD', $id)
            ->where('LoaiGiaoDich', 'payment')
            ->orderByDesc('ThoiGian')
            ->first();
        $rating = DanhGiaNhanVien::where('ID_DD', $id)->first();
        $paymentStatus = 'unknown';
        $paymentDeadline = null;
        if ($paymentRecord && $paymentRecord->PhuongThucThanhToan === 'VNPay') {
            $createdAt = $paymentRecord->ThoiGian ?: $booking->NgayTao;
            $paymentDeadline = $createdAt ? Carbon::parse($createdAt)->addMinutes(5) : null;
            if (empty($paymentRecord->MaGiaoDichVNPAY) || $paymentRecord->TrangThai === 'ChoXuLy') {
                if ($paymentDeadline && Carbon::now()->greaterThanOrEqualTo($paymentDeadline)) {
                    // Auto cancel booking if payment expired
                    $booking->TrangThaiDon = 'cancelled';
                    $booking->save();
                    $paymentRecord->TrangThai = 'ThatBai';
                    $paymentRecord->save();
                    $paymentStatus = 'failed';
                } else {
                    $paymentStatus = 'pending';
                }
            } elseif ($paymentRecord->MaGiaoDichVNPAY && $paymentRecord->TrangThai === 'ThanhCong') {
                $paymentStatus = 'success';
            } else {
                $paymentStatus = 'failed';
            }
        }
        
        // Get applied vouchers
        $vouchers = ChiTietKhuyenMai::where('ID_DD', $id)->get();

        // Collect month sessions if applicable
        $sessions = collect();
        $sessionData = collect();
        $firstSessionDate = $booking->NgayLam;
        $firstSessionStart = $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null;

        if ($booking->LoaiDon === 'month') {
            $sessions = LichBuoiThang::where('ID_DD', $id)
                ->orderBy('NgayLam')
                ->orderBy('GioBatDau')
                ->get();

            $sessionData = $sessions->map(function ($session) {
                return [
                    'id' => $session->ID_Buoi,
                    'date' => $session->NgayLam,
                    'start_time' => $session->GioBatDau ? substr($session->GioBatDau, 0, 5) : null,
                    'status' => $session->TrangThaiBuoi,
                    'staff_id' => $session->ID_NV,
                ];
            });

            if ($sessions->isNotEmpty()) {
                $first = $sessions->first();
                $firstSessionDate = $firstSessionDate ?? $first->NgayLam;
                $firstSessionStart = $firstSessionStart ?? ($first->GioBatDau ? substr($first->GioBatDau, 0, 5) : null);
            }
        }

        $sessionCounts = [
            'total' => $sessionData->count(),
            'completed' => $sessions->where('TrangThaiBuoi', 'completed')->count(),
            'cancelled' => $sessions->where('TrangThaiBuoi', 'cancelled')->count(),
        ];
        $sessionCounts['upcoming'] = max(0, $sessionCounts['total'] - $sessionCounts['completed'] - $sessionCounts['cancelled']);
        $canRescheduleFindingStaff = $booking->LoaiDon === 'hour'
            && $booking->TrangThaiDon === 'finding_staff'
            && $booking->FindingStaffPromptSentAt !== null
            && ($booking->RescheduleCount ?? 0) < 1;
        $suggestedNearestTime = $this->suggestNearestAvailableTime($booking);

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
                'work_date' => $firstSessionDate,
                'start_time' => $firstSessionStart,
                'duration_hours' => (float) $booking->ThoiLuongGio,
                'status' => $booking->TrangThaiDon,
                'total_amount' => (float) $booking->TongTien,
                'discounted_amount' => (float) $booking->TongTienSauGiam,
                'staff_id' => $booking->ID_NV,
                'staff' => $staff ? [
                    'id' => $staff->ID_NV,
                    'name' => $staff->Ten_NV,
                    'phone' => $staff->SDT,
                    'avatar' => $this->normalizeImageUrl($staff->HinhAnh),
                ] : null,
                'payment_method' => $this->latestPaymentMethod($booking->ID_DD),
                'payment_status' => $paymentStatus,
                'payment_deadline' => $paymentDeadline ? $paymentDeadline->toDateTimeString() : null,
                'created_at' => $booking->NgayTao,
                'vouchers' => $vouchers->map(function ($v) {
                    return [
                        'voucher_code' => $v->ID_KM,
                        'discount_amount' => (float) $v->TienGiam,
                    ];
                }),
                'sessions' => $sessionData,
                'session_counts' => $sessionCounts,
                'rating' => $rating ? [
                    'id' => $rating->ID_DG,
                    'score' => (int) $rating->Diem,
                    'comment' => $rating->NhanXet,
                    'created_at' => $rating->ThoiGian,
                ] : null,
                'can_rate' => in_array($booking->TrangThaiDon, ['completed', 'done'], true)
                    && !$rating,
                'finding_staff_prompt_sent_at' => $booking->FindingStaffPromptSentAt,
                'finding_staff_response' => $booking->FindingStaffResponse,
                'reschedule_count' => (int) ($booking->RescheduleCount ?? 0),
                'can_reschedule' => $canRescheduleFindingStaff,
                'suggested_time' => $suggestedNearestTime,
            ]
        ]);
    }

    /**
     * Create new booking
     * POST /api/bookings
     */
    public function store(Request $request, SurchargeService $surchargeService, VNPayService $vnPayService)
    {
        $validator = \Validator::make($request->all(), [
            'order_type' => ['required', 'in:hour,month'],
            'service_id' => ['required', 'string'],
            'address_id' => ['nullable', 'string'],
            'address_text' => ['nullable', 'string'],
            'address_unit' => ['nullable', 'string'],
            'work_date' => ['nullable', 'date'],
            'start_time' => ['required_if:order_type,month', 'date_format:H:i'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'discounted_amount' => ['nullable', 'numeric', 'min:0'],
            'staff_id' => ['nullable', 'string'],
            'vouchers' => ['nullable', 'array'],
            'vouchers.*.code' => ['required', 'string'],
            'vouchers.*.discount_amount' => ['nullable', 'numeric'],
            'has_pets' => ['nullable', 'boolean'],
            'repeat_days' => ['required_if:order_type,month', 'array', 'min:1'],
            'repeat_days.*' => ['integer', 'between:0,6'],
            'package_months' => ['required_if:order_type,month', 'integer', 'in:1,2,3,6'],
            'repeat_start_date' => ['nullable', 'date'],
            'repeat_end_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'in:cash,vnpay'],
            'return_url' => ['nullable', 'string'],
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
        $returnUrlOverride = $request->return_url;
        $paymentUrl = null;

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

            // Luon tao dia chi tam thoi (khong gan ID_KH) de khong luu vao danh sach dia chi da luu
            $quan = $this->guessQuanFromAddress($addressText);
            $newIdDc = IdGenerator::next('DiaChi', 'ID_DC', 'DC_');

            DiaChi::create([
                'ID_DC' => $newIdDc,
                'ID_KH' => null,
                'ID_Quan' => $quan?->ID_Quan,
                'CanHo' => $addressUnit,
                'DiaChiDayDu' => $addressText,
            ]);

            $idDc = $newIdDc;
        }
        // Prepare booking data
        $prefix = $request->order_type === 'month' ? 'DD_month_' : 'DD_hour_';
        $idDon = IdGenerator::next('DonDat', 'ID_DD', $prefix);

        $gioBatDau = $request->start_time ? $request->start_time . ':00' : null;
        $hasPets = $request->has('has_pets') ? (bool) $request->has_pets : false;
        $repeatDays = is_array($request->repeat_days)
            ? array_values(array_unique(array_map('intval', $request->repeat_days)))
            : [];
        $ngayLam = $request->order_type === 'hour' ? $request->work_date : null;

        $idGoi = null;
        $startDate = null;
        $endDateExclusive = null;
        $sessionCount = 1;
        $weekendSessionCount = 0;

        if ($request->order_type === 'month') {
            $packageMonths = (int) ($request->package_months ?? 0);

            if (empty($repeatDays)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Vui lòng chọn thứ lặp lại.',
                ], 422);
            }

            $goiMap = [
                1 => 'GT01',
                2 => 'GT02',
                3 => 'GT03',
                6 => 'GT04',
            ];

            $idGoi = $goiMap[$packageMonths] ?? null;
            if ($idGoi === null) {
                $idGoi = GoiThang::where('SoNgay', $packageMonths * 30)->value('ID_Goi');
            }

            if ($idGoi === null) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gói tháng không hợp lệ.',
                ], 422);
            }

            $defaultSoNgay = $packageMonths > 0 ? $packageMonths * 30 : 0;
            $soNgayDb = GoiThang::where('ID_Goi', $idGoi)->value('SoNgay');
            $soNgay = $soNgayDb ?: $defaultSoNgay;

            if ($soNgay <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Số ngày hiệu lực gói không hợp lệ.',
                ], 422);
            }

            if ($request->repeat_start_date) {
                try {
                    $startDate = Carbon::parse($request->repeat_start_date)->startOfDay();
                } catch (\Throwable) {
                    $startDate = null;
                }
            }

            if ($startDate === null) {
                $startDate = $this->computePackageStartDate(Carbon::now(), $repeatDays);
            }

            $endDateExclusive = $startDate->copy()->addDays($soNgay);

            $sessionCount = $this->countSessionsInRange($repeatDays, $startDate, $endDateExclusive);
            $weekendDays = array_intersect($repeatDays, [0, 6]);
            $weekendSessionCount = empty($weekendDays)
                ? 0
                : $this->countSessionsInRange($weekendDays, $startDate, $endDateExclusive);
        }

        $service = DichVu::findOrFail($request->service_id);

        if ($request->order_type === 'month') {
            $basePrice = (float) $service->GiaDV;
            $package = $idGoi ? GoiThang::find($idGoi) : null;
            $packagePercent = $package && $package->PhanTramGiam !== null
                ? (float) $package->PhanTramGiam
                : 0.0;

            $gross = $basePrice * $sessionCount;
            $packageDiscount = $gross * $packagePercent / 100;
            $tongTien = max(0, $gross - $packageDiscount);
        } else {
            $tongTien = (float) $request->total_amount;
        }

        $tongSauGiam = $request->has('discounted_amount') && $request->discounted_amount !== null
            ? (float) $request->discounted_amount
            : $tongTien;

        $surchargeResult = $surchargeService->calculate(
            $request->order_type,
            $ngayLam,
            $gioBatDau,
            $request->order_type === 'month' ? $repeatDays : [],
            $hasPets,
            $sessionCount,
            $weekendSessionCount
        );

        $tongTien += $surchargeResult['total'];
        $tongSauGiam += $surchargeResult['total'];

        // Giu trang thai theo logic nhan vien, khong them trang thai moi
        $trangThaiDon = $request->staff_id ? 'assigned' : 'finding_staff';

        $booking = DonDat::create([
            'ID_DD' => $idDon,
            'LoaiDon' => $request->order_type,
            'ID_DV' => $request->service_id,
            'ID_KH' => $khachHang->ID_KH,
            'ID_DC' => $idDc,
            'GhiChu' => $request->note,
            'NgayLam' => $ngayLam,
            'GioBatDau' => $gioBatDau,
            'ThoiLuongGio' => $request->duration_hours,
            'ID_Goi' => $idGoi,
            'NgayBatDauGoi' => $startDate ? $startDate->toDateString() : null,
            'NgayKetThucGoi' => $endDateExclusive
                ? $endDateExclusive->copy()->subDay()->toDateString()
                : null,
            'TrangThaiDon' => $trangThaiDon,
            'TongTien' => $tongTien,
            'TongTienSauGiam' => $tongSauGiam,
            'ID_NV' => $request->staff_id,
        ]);

        if ($request->order_type === 'month') {
            foreach ($repeatDays as $day) {
                LichTheoTuan::create([
                    'ID_LichTuan' => IdGenerator::next('LichTheoTuan', 'ID_LichTuan', 'LTT'),
                    'ID_DD' => $idDon,
                    'Thu' => $day,
                    'GioBatDau' => $gioBatDau,
                ]);
            }

            if ($startDate && $endDateExclusive) {
                $cursor = $startDate->copy();
                $endExclusive = $endDateExclusive->copy();

                while ($cursor->lt($endExclusive)) {
                    if (in_array($cursor->dayOfWeek, $repeatDays, true)) {
                        LichBuoiThang::create([
                            'ID_Buoi' => IdGenerator::next('LichBuoiThang', 'ID_Buoi', 'LBT_'),
                            'ID_DD' => $idDon,
                            'NgayLam' => $cursor->toDateString(),
                            'GioBatDau' => $gioBatDau,
                            'TrangThaiBuoi' => 'finding_staff',
                            'ID_NV' => null,
                        ]);
                    }
                    $cursor->addDay();
                }
            }
        }

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

        // Create payment record and generate VNPay URL if needed
        if ($paymentMethod === 'vnpay') {
            $baseReturnUrl = config('vnpay.return_url');
            $returnUrl = $baseReturnUrl;
            if ($returnUrlOverride) {
                $separator = str_contains($baseReturnUrl, '?') ? '&' : '?';
                $returnUrl = $baseReturnUrl . $separator . 'app_redirect=' . urlencode($returnUrlOverride);
            }

            $paymentUrl = $vnPayService->createPaymentUrl([
                'txn_ref' => $idDon,
                'amount' => $tongSauGiam,
                'order_info' => 'Thanh toan don dat ' . $idDon,
                'return_url' => $returnUrl,
            ]);
        }

        LichSuThanhToan::create([
            'ID_LSTT' => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
            'PhuongThucThanhToan' => $paymentMethod === 'vnpay' ? 'VNPay' : 'TienMat',
            'TrangThai' => $paymentMethod === 'cash' ? 'ThanhCong' : 'ChoXuLy',
            'SoTienThanhToan' => $tongSauGiam,
            'MaGiaoDichVNPAY' => null,
            'ThoiGian' => now(),
            'ID_DD' => $idDon,
        ]);

        // Notify customer about new booking
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyOrderCreated($booking);
        } catch (\Exception $e) {
            // ignore notification errors
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn đặt thành công.',
            'data' => [
                'booking_id' => $idDon,
                'status' => $trangThaiDon,
                'payment_url' => $paymentUrl,
                'payment_deadline' => $paymentMethod === 'vnpay'
                    ? now()->addMinutes(5)->toDateTimeString()
                    : null,
            ]
        ], 201);
    }

    /**
     * Customer rates a completed booking
     * POST /api/bookings/{id}/rate
     */
    public function rate(Request $request, string $id, NotificationService $notificationService)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;
        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.'
            ], 403);
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

        if (!in_array($booking->TrangThaiDon, ['completed', 'done'], true)) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ đánh giá khi đơn đã hoàn tất.'
            ], 422);
        }

        if (DanhGiaNhanVien::where('ID_DD', $booking->ID_DD)->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Bạn đã đánh giá đơn này.'
            ], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        DanhGiaNhanVien::create([
            'ID_DG' => IdGenerator::next('DanhGiaNhanVien', 'ID_DG', 'DG_'),
            'ID_DD' => $booking->ID_DD,
            'ID_NV' => $booking->ID_NV,
            'ID_KH' => $khachHang->ID_KH,
            'Diem' => $validated['rating'],
            'NhanXet' => $validated['comment'] ?? null,
            'ThoiGian' => now(),
        ]);

        // Move booking to done so it sits in history and notify customer of status change
        if ($booking->TrangThaiDon === 'completed') {
            $oldStatus = $booking->TrangThaiDon;
            $booking->TrangThaiDon = 'done';
            $booking->save();
            try {
                $notificationService->notifyOrderStatusChanged($booking, $oldStatus, 'done');
            } catch (\Exception $e) {
                Log::error('Failed to send status change notification after rating (API)', [
                    'booking_id' => $booking->ID_DD,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu đánh giá.',
            'data' => [
                'rating' => [
                    'score' => (int) $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                    'created_at' => now()->toDateTimeString(),
                ],
                'status' => $booking->TrangThaiDon,
            ],
        ]);
    }

    /**
     * Handle finding-staff prompt (wait / reschedule)
     * POST /api/bookings/{id}/finding-staff-action
     */
    public function findingStaffAction(Request $request, string $id, VNPayService $vnPay, NotificationService $notificationService)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;
        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Vui lòng đăng nhập khách hàng.',
            ], 403);
        }

        $booking = DonDat::with('lichSuThanhToan')
            ->where('ID_DD', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn đặt.',
            ], 404);
        }

        if ($booking->LoaiDon !== 'hour' || $booking->TrangThaiDon !== 'finding_staff') {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ hỗ trợ đơn theo giờ đang tìm nhân viên.',
            ], 422);
        }

        if (($booking->RescheduleCount ?? 0) >= 1) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ được thay đổi thời gian 1 lần.',
            ], 422);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:wait,reschedule'],
            'new_date' => ['required_if:action,reschedule', 'date'],
            'new_time' => ['required_if:action,reschedule', 'date_format:H:i'],
        ]);

        if ($validated['action'] === 'wait') {
            $booking->FindingStaffResponse = 'wait';
            if (!$booking->FindingStaffPromptSentAt) {
                $booking->FindingStaffPromptSentAt = now();
            }
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã ghi nhận bạn tiếp tục chờ.',
            ]);
        }

        $newDate = $validated['new_date'];
        $newTime = $validated['new_time'];
        $newStart = Carbon::parse($newDate . ' ' . $newTime);

        if ($newStart->hour < 7 || $newStart->hour > 17) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ chấp nhận thay đổi trong khung 07:00 - 17:00.',
            ], 422);
        }

        if ($newStart->lessThanOrEqualTo(Carbon::now())) {
            return response()->json([
                'success' => false,
                'error' => 'Thời gian mới phải lớn hơn hiện tại.',
            ], 422);
        }

        $oldHour = $booking->GioBatDau ? Carbon::parse($booking->GioBatDau)->hour : null;
        $newHour = $newStart->hour;

        $surchargeAmount = 30000;
        $hasPt001 = \App\Models\ChiTietPhuThu::where('ID_DD', $booking->ID_DD)
            ->where('ID_PT', 'PT001')
            ->exists();

        $needsSurcharge = ($newHour < 8 || $newHour == 17)
            && ($oldHour === null || !($oldHour < 8 || $oldHour == 17))
            && !$hasPt001;

        $paymentUrl = null;

        $booking->NgayLam = $newDate;
        $booking->GioBatDau = $newStart->format('H:i:s');
        $booking->FindingStaffResponse = 'reschedule';
        $booking->RescheduleCount = ($booking->RescheduleCount ?? 0) + 1;

        if ($needsSurcharge) {
            \App\Models\ChiTietPhuThu::create([
                'ID_PT' => 'PT001',
                'ID_DD' => $booking->ID_DD,
                'Ghichu' => 'Phụ thu đổi giờ 7h/17h',
            ]);

            $booking->TongTien = ($booking->TongTien ?? 0) + $surchargeAmount;
            $booking->TongTienSauGiam = ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0) + $surchargeAmount;

            $payment = $booking->lichSuThanhToan->first();
            $paymentMethod = $payment?->PhuongThucThanhToan ?? 'TienMat';

            if ($paymentMethod === 'VNPay') {
                $paymentId = IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_');

                LichSuThanhToan::create([
                    'ID_LSTT' => $paymentId,
                    'PhuongThucThanhToan' => 'VNPay',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $surchargeAmount,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => 'Phu thu doi gio cao diem (truoc 8h hoac 17h)',
                    'ThoiGian' => now(),
                ]);

                $paymentUrl = $vnPay->createPaymentUrl([
                    'txn_ref' => $paymentId,
                    'amount' => $surchargeAmount,
                    'order_info' => 'Phu thu doi gio don ' . $booking->ID_DD,
                ]);
            } else {
                LichSuThanhToan::create([
                    'ID_LSTT' => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
                    'PhuongThucThanhToan' => 'TienMat',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $surchargeAmount,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => 'Phu thu doi gio cao diem (truoc 8h hoac 17h)',
                    'ThoiGian' => now(),
                ]);
            }
        }

        $booking->save();

        try {
            $notificationService->notifyOrderRescheduled($booking);
        } catch (\Exception $e) {
            Log::error('Failed to send reschedule notification (API)', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'requires_payment' => $paymentUrl !== null,
            'payment_url' => $paymentUrl,
            'message' => $needsSurcharge
                ? 'Đã cập nhật thời gian và thêm phụ thu 30,000d.'
                : 'Đã cập nhật thời gian bắt đầu đơn.',
        ]);
    }

    /**
     * Get staff/time suggestions for finding_staff order
     * GET /api/bookings/{id}/suggestions
     */
    public function suggestions(Request $request, string $id)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;
        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Vui lòng đăng nhập khách hàng.',
            ], 403);
        }

        $booking = DonDat::with('diaChi', 'dichVu')
            ->where('ID_DD', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn đặt.',
            ], 404);
        }

        if ($booking->LoaiDon !== 'hour' || $booking->TrangThaiDon !== 'finding_staff') {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ hỗ trợ đơn theo giờ đang tìm nhân viên.',
            ], 422);
        }

        $suggestions = $this->getSuggestedStaffAndTime($booking);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Apply a suggestion (assign staff + new time)
     * POST /api/bookings/{id}/apply-suggestion
     */
    public function applySuggestion(Request $request, string $id, VNPayService $vnPay, NotificationService $notificationService)
    {
        $user = $request->user();
        $khachHang = $user->khachHang;
        if (!$khachHang) {
            return response()->json([
                'success' => false,
                'error' => 'Vui lòng đăng nhập khách hàng.',
            ], 403);
        }

        $booking = DonDat::with(['dichVu', 'lichSuThanhToan'])
            ->where('ID_DD', $id)
            ->where('ID_KH', $khachHang->ID_KH)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn đặt.',
            ], 404);
        }

        if ($booking->LoaiDon !== 'hour' || $booking->TrangThaiDon !== 'finding_staff') {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ hỗ trợ đơn theo giờ đang tìm nhân viên.',
            ], 422);
        }

        if (($booking->RescheduleCount ?? 0) >= 1) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ được thay đổi thời gian 1 lần.',
            ], 422);
        }

        $validated = $request->validate([
            'id_nv' => ['required', 'string', 'exists:NhanVien,ID_NV'],
            'suggested_date' => ['required', 'date'],
            'suggested_time' => ['required', 'date_format:H:i'],
        ]);

        $newDate = $validated['suggested_date'];
        $newTime = $validated['suggested_time'];
        $newStaffId = $validated['id_nv'];

        $newStart = Carbon::parse($newDate . ' ' . $newTime);
        if ($newStart->hour < 7 || $newStart->hour > 17) {
            return response()->json([
                'success' => false,
                'error' => 'Giờ bắt đầu phải trong khung 07:00 - 17:00',
            ], 422);
        }

        if ($newStart->lessThanOrEqualTo(Carbon::now())) {
            return response()->json([
                'success' => false,
                'error' => 'Thời gian phải lớn hơn hiện tại.',
            ], 422);
        }

        $oldHour = $booking->GioBatDau ? Carbon::parse($booking->GioBatDau)->hour : null;
        $newHour = $newStart->hour;

        $surchargeAmount = 30000;
        $hasPt001 = \App\Models\ChiTietPhuThu::where('ID_DD', $booking->ID_DD)
            ->where('ID_PT', 'PT001')
            ->exists();

        $needsSurcharge = ($newHour < 8 || $newHour == 17)
            && ($oldHour === null || !($oldHour < 8 || $oldHour == 17))
            && !$hasPt001;

        $paymentUrl = null;

        $booking->NgayLam = $newDate;
        $booking->GioBatDau = $newStart->format('H:i:s');
        $booking->ID_NV = $newStaffId;
        $booking->TrangThaiDon = 'assigned';
        $booking->FindingStaffResponse = 'reschedule';
        $booking->RescheduleCount = ($booking->RescheduleCount ?? 0) + 1;

        if ($needsSurcharge) {
            \App\Models\ChiTietPhuThu::create([
                'ID_PT' => 'PT001',
                'ID_DD' => $booking->ID_DD,
                'Ghichu' => 'Phu thu doi gio 7h/17h',
            ]);

            $booking->TongTien = ($booking->TongTien ?? 0) + $surchargeAmount;
            $booking->TongTienSauGiam = ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0) + $surchargeAmount;

            $payment = $booking->lichSuThanhToan->first();
            $paymentMethod = $payment?->PhuongThucThanhToan ?? 'TienMat';

            if ($paymentMethod === 'VNPay') {
                $paymentId = IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_');

                LichSuThanhToan::create([
                    'ID_LSTT' => $paymentId,
                    'PhuongThucThanhToan' => 'VNPay',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $surchargeAmount,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => 'Phu thu doi gio cao diem (truoc 8h hoac 17h)',
                    'ThoiGian' => now(),
                ]);

                $paymentUrl = $vnPay->createPaymentUrl([
                    'txn_ref' => $paymentId,
                    'amount' => $surchargeAmount,
                    'order_info' => 'Phu thu doi gio don ' . $booking->ID_DD,
                ]);
            } else {
                LichSuThanhToan::create([
                    'ID_LSTT' => IdGenerator::next('LichSuThanhToan', 'ID_LSTT', 'LSTT_'),
                    'PhuongThucThanhToan' => 'TienMat',
                    'TrangThai' => 'ChoXuLy',
                    'SoTienThanhToan' => $surchargeAmount,
                    'ID_DD' => $booking->ID_DD,
                    'LoaiGiaoDich' => 'payment',
                    'GhiChu' => 'Phu thu doi gio cao diem (truoc 8h hoac 17h)',
                    'ThoiGian' => now(),
                ]);
            }
        }

        $booking->save();

        try {
            $this->notifyStaffAssigned($booking);
        } catch (\Exception $e) {
            Log::error('Failed to notify staff assigned after suggestion (API)', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $notificationService->notifyOrderRescheduled($booking);
        } catch (\Exception $e) {
            Log::error('Failed to send reschedule notification (API apply suggestion)', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'requires_payment' => $paymentUrl !== null,
            'payment_url' => $paymentUrl,
            'message' => $needsSurcharge
                ? 'Đã cập nhật đơn và thêm phụ thu 30,000d.'
                : 'Đã cập nhật đơn và gán nhân viên.',
            'data' => [
                'new_date' => $newStart->format('d/m/Y'),
                'new_time' => $newStart->format('H:i'),
                'staff_name' => NhanVien::find($newStaffId)?->Ten_NV ?? '',
                'surcharge_added' => $needsSurcharge,
                'surcharge_amount' => $needsSurcharge ? $surchargeAmount : 0,
            ],
        ]);
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

        $start = Carbon::parse("$ngayLam $gioBatDau");
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

            // Skip staff who is busy (assigned/confirmed) with overlapping or tight-gap bookings
            $candidateStart = Carbon::parse("$ngayLam $gioBatDau");
            $candidateEnd = $candidateStart->copy()->addHours($thoiLuong);
            $busyBookings = DonDat::where('ID_NV', $nv->ID_NV)
                ->whereIn('TrangThaiDon', ['assigned', 'confirmed'])
                ->whereDate('NgayLam', $ngayLam)
                ->get();

            $conflict = false;
            foreach ($busyBookings as $busy) {
                if (!$busy->GioBatDau || !$busy->ThoiLuongGio) {
                    continue;
                }
                $busyStart = Carbon::parse($busy->NgayLam . ' ' . $busy->GioBatDau);
                $busyEnd = $busyStart->copy()->addHours((float) $busy->ThoiLuongGio);

                // Overlap
                if ($candidateStart->lt($busyEnd) && $candidateEnd->gt($busyStart)) {
                    $conflict = true;
                    break;
                }

                // Gap <= 1 hour after busy booking ends
                if ($candidateStart->lt($busyEnd->copy()->addHour())) {
                    $conflict = true;
                    break;
                }
            }

            if ($conflict) {
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
        public function cancel(Request $request, $id, RefundService $refundService, NotificationService $notificationService)
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

        if (in_array($booking->TrangThaiDon, ['done', 'cancelled', 'completed', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'error' => 'Không thể hủy đơn đặt này.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $booking->TrangThaiDon = 'cancelled';
            $booking->save();

            if ($booking->LoaiDon === 'month') {
                LichBuoiThang::where('ID_DD', $booking->ID_DD)
                    ->whereIn('TrangThaiBuoi', ['finding_staff', 'assigned'])
                    ->update([
                        'TrangThaiBuoi' => 'cancelled',
                        'ID_NV' => null,
                    ]);
            }

            $refundResult = $refundService->refundOrder($booking, 'user_cancel');
            $notificationService->notifyOrderCancelled($booking, 'user_cancel', $refundResult);

            DB::commit();

            $message = 'Hủy đơn đặt thành công.';
            if (!empty($refundResult['amount'])) {
                $message .= ' Hoàn ' . number_format($refundResult['amount']) . ' VND.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'refund' => $refundResult,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Không thể hủy đơn: ' . $e->getMessage(),
            ], 500);
        }
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

    /**
     * Suggest nearest time in 07-17 with ready staff
     */
    private function suggestNearestAvailableTime(DonDat $booking): ?string
    {
        if ($booking->LoaiDon !== 'hour' || !$booking->NgayLam || !$booking->GioBatDau) {
            return null;
        }

        try {
            $base = Carbon::parse($booking->GioBatDau);
        } catch (\Exception) {
            return null;
        }

        $duration = $booking->ThoiLuongGio ?? ($booking->dichVu->ThoiLuong ?? 2);
        $hours = range(7, 17);
        $candidates = [];
        foreach ($hours as $h) {
            $diff = abs($base->hour - $h);
            $candidates[] = ['hour' => $h, 'diff' => $diff];
        }

        usort($candidates, function ($a, $b) {
            if ($a['diff'] === $b['diff']) {
                return $a['hour'] <=> $b['hour'];
            }
            return $a['diff'] <=> $b['diff'];
        });

        foreach ($candidates as $cand) {
            $start = Carbon::parse($booking->NgayLam . ' ' . sprintf('%02d:00:00', $cand['hour']));
            $end = $start->copy()->addHours((float) $duration);

            $hasStaff = LichLamViec::where('NgayLam', $booking->NgayLam)
                ->where('TrangThai', 'ready')
                ->where('GioBatDau', '<=', $start->format('H:i:s'))
                ->where('GioKetThuc', '>=', $end->format('H:i:s'))
                ->exists();

            if ($hasStaff) {
                return $start->format('H:i');
            }
        }

        return null;
    }

    /**
     * Get top staff suggestions with nearest available time
     */
    private function getSuggestedStaffAndTime(DonDat $booking): array
    {
        if ($booking->LoaiDon !== 'hour' || !$booking->NgayLam || !$booking->GioBatDau) {
            return [];
        }

        $duration = $booking->ThoiLuongGio ?? ($booking->dichVu->ThoiLuong ?? 2);
        $originalDate = Carbon::parse($booking->NgayLam);

        $customerQuan = null;
        if ($booking->diaChi) {
            $diaChiText = $booking->diaChi->DiaChiDayDu ?? '';
            if ($diaChiText !== '') {
                $customerQuan = $this->guessQuanFromAddress($diaChiText);
            }
        }

        $suggestions = [];
        $datesToSearch = [];
        for ($i = 0; $i <= 3; $i++) {
            if ($i === 0) {
                $datesToSearch[] = $originalDate->copy();
            } else {
                $datesToSearch[] = $originalDate->copy()->addDays($i);
                $datesToSearch[] = $originalDate->copy()->subDays($i);
            }
        }

        foreach ($datesToSearch as $searchDate) {
            if ($searchDate->isPast()) {
                continue;
            }

            $ngayLam = $searchDate->format('Y-m-d');
            $lichLamViec = LichLamViec::with('nhanVien')
                ->where('NgayLam', $ngayLam)
                ->where('TrangThai', 'ready')
                ->get();

            foreach ($lichLamViec as $lich) {
                $nv = $lich->nhanVien;
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

                $scheduleStart = Carbon::parse($lich->GioBatDau);
                $scheduleEnd = Carbon::parse($lich->GioKetThuc);

                $bestTime = null;
                $minDiff = PHP_INT_MAX;

                $currentTime = $scheduleStart->copy();
                while ($currentTime->copy()->addHours($duration)->lessThanOrEqualTo($scheduleEnd)) {
                    $potentialEnd = $currentTime->copy()->addHours($duration);
                    $hasConflict = $this->hasTimeConflict(
                        $nv->ID_NV,
                        $ngayLam,
                        $currentTime->format('H:i:s'),
                        $potentialEnd->format('H:i:s')
                    );

                    if (!$hasConflict) {
                        $proposedDateTime = Carbon::parse($ngayLam . ' ' . $currentTime->format('H:i:s'));
                        $originalDateTime = Carbon::parse($booking->NgayLam . ' ' . $booking->GioBatDau);
                        $diff = abs($proposedDateTime->diffInMinutes($originalDateTime));

                        if ($diff < $minDiff) {
                            $minDiff = $diff;
                            $bestTime = $currentTime->copy();
                        }
                    }

                    $currentTime->addMinutes(30);
                }

                if ($bestTime) {
                    $hinhAnh = $nv->HinhAnh;
                    if (empty($hinhAnh)) {
                        $hinhAnh = 'https://ui-avatars.com/api/?name=' . urlencode($nv->Ten_NV) . '&background=004d2e&color=fff&size=150';
                    } elseif (!str_starts_with($hinhAnh, 'http')) {
                        $hinhAnh = url('storage/' . ltrim($hinhAnh, '/'));
                    }

                    $jobsCompleted = DonDat::where('ID_NV', $nv->ID_NV)
                        ->where('TrangThaiDon', 'done')
                        ->count();

                    $score = $ratingPercent * 0.3 + $proximityPercent * 0.7;
                    $daysDiff = abs($searchDate->diffInDays($originalDate));
                    $datePenalty = $daysDiff * 10;
                    $adjustedScore = max(0, $score - $datePenalty);

                    $suggestions[] = [
                        'id_nv' => $nv->ID_NV,
                        'ten_nv' => $nv->Ten_NV,
                        'hinh_anh' => $hinhAnh,
                        'sdt' => $nv->SDT,
                        'rating_percent' => $ratingPercent,
                        'avg_rating' => $avgScore ? round($avgScore, 1) : 0,
                        'proximity_percent' => $proximityPercent,
                        'score' => (float) $adjustedScore,
                        'jobs_completed' => $jobsCompleted,
                        'suggested_date' => $searchDate->format('Y-m-d'),
                        'suggested_time' => $bestTime->format('H:i'),
                        'time_diff_minutes' => $minDiff,
                        'days_diff' => $daysDiff,
                    ];
                }
            }

            if (count($suggestions) >= 10) {
                break;
            }
        }

        usort($suggestions, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($suggestions, 0, 3);
    }

    private function hasTimeConflict(string $staffId, string $date, string $startTime, string $endTime): bool
    {
        return DonDat::where('ID_NV', $staffId)
            ->where('NgayLam', $date)
            ->whereNotIn('TrangThaiDon', ['cancelled', 'rejected'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereRaw('GioBatDau < ?', [$endTime])
                    ->whereRaw('ADDTIME(GioBatDau, SEC_TO_TIME(ThoiLuongGio * 3600)) > ?', [$startTime]);
            })
            ->exists();
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

    /**
     * Normalize storage/localhost image URLs for staff avatar
     */
    private function normalizeImageUrl(?string $url): ?string
    {
        if (!$url || $url === '') {
            return null;
        }

        try {
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                $parsed = parse_url($url);
                if (!$parsed || !isset($parsed['host'])) {
                    return $url;
                }
                // Replace emulator/localhost with current host
                if (in_array($parsed['host'], ['10.0.2.2', '127.0.0.1', 'localhost'], true)) {
                    $current = request()->getSchemeAndHttpHost();
                    $path = $parsed['path'] ?? '';
                    return rtrim($current, '/') . $path;
                }
                return $url;
            }

            // Storage path
            if (str_starts_with($url, '/')) {
                return url($url);
            }

            return url(Storage::url($url));
        } catch (\Throwable $e) {
            return $url;
        }
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

    private function countSessionsInRange(array $weekdays, $startDate, $endDateExclusive): int
    {
        $normalizedDays = array_unique(array_map('intval', $weekdays));
        sort($normalizedDays);

        $start = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();
        $endExclusive = $endDateExclusive instanceof Carbon ? $endDateExclusive->copy()->startOfDay() : Carbon::parse($endDateExclusive)->startOfDay();

        $count = 0;
        $cursor = $start->copy();

        while ($cursor->lt($endExclusive)) {
            if (in_array((int) $cursor->dayOfWeek, $normalizedDays, true)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    private function computePackageStartDate(Carbon $orderDate, array $weekdays): Carbon
    {
        $normalizedDays = array_values(array_unique(array_map('intval', $weekdays)));
        $normalizedDays = array_filter($normalizedDays, static fn ($d) => $d >= 0 && $d <= 6);

        return $orderDate->copy()->addDays(3)->startOfDay();
    }

    /**
     * Get latest payment method for booking (from history)
     */
    private function latestPaymentMethod(string $bookingId): ?string
    {
        $record = LichSuThanhToan::where('ID_DD', $bookingId)
            ->orderByDesc('ThoiGian')
            ->first();

        return $record?->PhuongThucThanhToan;
    }
}
