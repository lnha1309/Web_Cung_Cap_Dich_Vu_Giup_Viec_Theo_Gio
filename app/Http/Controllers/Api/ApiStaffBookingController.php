<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\DonDat;
use App\Models\LichSuThanhToan;
use App\Models\LichLamViec;
use App\Models\LichBuoiThang;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Models\DanhGiaNhanVien;
use App\Models\TaiKhoan;
use App\Services\NotificationService;
use App\Services\StaffWalletService;
use App\Services\RefundService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ApiStaffBookingController extends Controller
{
    private function requireStaff(Request $request)
    {
        $taiKhoan = $request->user();
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return null;
        }

        return $nhanVien;
    }

    /**
     * Danh sach don dang mo de nhan, loai bo don trung gio voi lich da nhan cua nhan vien
     * GET /api/staff/bookings/available
     */
    public function available(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được đơn.',
            ], 403);
        }

        $busy = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiDon', ['assigned', 'confirmed'])
            ->get()
            ->map(function (DonDat $booking) {
                if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
                    return null;
                }
                $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
                $end = $start->copy()->addHours((float) $booking->ThoiLuongGio);

                return [
                    'date' => $booking->NgayLam,
                    'start' => $start,
                    'end' => $end,
                ];
            })
            ->filter()
            ->values()
            ->toBase();

        $nowPlus2h = Carbon::now()->addHours(2);

        $sevenDaysAhead = Carbon::now()->addDays(7)->endOfDay();

        $candidates = DonDat::whereIn('TrangThaiDon', ['finding_staff', 'rejected'])
            ->with(['khachHang', 'diaChi'])
            ->orderBy('NgayLam')
            ->get()
            ->filter(function (DonDat $booking) use ($busy, $nowPlus2h, $sevenDaysAhead) {
                if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
                    return false;
                }

                $durationHours = (float) $booking->ThoiLuongGio;
                if ($durationHours < 2) {
                    return false;
                }

                $startTime = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
                $bookingStart = Carbon::parse($booking->NgayLam)
                    ->setTime($startTime->hour, $startTime->minute, $startTime->second);
                if ($bookingStart->lt($nowPlus2h) || $bookingStart->gt($sevenDaysAhead)) {
                    return false;
                }

                $start = $startTime;
                $end = $start->copy()->addHours($durationHours);

                // loại nếu trùng giờ với bất kỳ đơn đã/đang nhận
                foreach ($busy as $b) {
                    if ($b['date'] !== $booking->NgayLam) {
                        continue;
                    }
                    $bufferedEnd = $b['end']->copy()->addHour(); // Require at least 1h gap after accepted job
                    if ($this->overlaps($start, $end, $b['start'], $b['end'])) {
                        return false;
                    }
                    if ($start->gte($b['start']) && $start->lt($bufferedEnd)) {
                        return false;
                    }
                }

                return true;
            })
            ->map(function (DonDat $booking) {
                $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
                $end = $start->copy()->addHours((float) $booking->ThoiLuongGio);
                $kh = $booking->khachHang;
                $address = $booking->diaChi;
                return [
                    'id' => $booking->ID_DD,
                    'service_id' => $booking->ID_DV,
                    'work_date' => $booking->NgayLam,
                    'start_time' => $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null,
                    'duration_hours' => (float) $booking->ThoiLuongGio,
                    'end_time' => $end->format('H:i'),
                    'status' => $booking->TrangThaiDon,
                    'note' => $booking->GhiChu,
                    'customer_name' => $kh?->Ten_KH,
                    'customer_phone' => $kh?->SDT,
                    'address' => $address ? [
                        'id' => $address->ID_DC,
                        'unit' => $address->CanHo,
                        'full_address' => $address->DiaChiDayDu,
                    ] : null,
                ];
            })
            ->values();

        // Monthly sessions waiting for staff
        $monthlySessions = LichBuoiThang::whereIn('TrangThaiBuoi', ['finding_staff', 'rejected'])
            ->whereNull('ID_NV')
            ->with(['donDat.khachHang', 'donDat.diaChi'])
            ->orderBy('NgayLam')
            ->get()
            ->filter(function (LichBuoiThang $session) use ($nhanVien, $nowPlus2h, $sevenDaysAhead) {
                $booking = $session->donDat;
                if (!$booking || !$session->NgayLam || !$session->GioBatDau) {
                    return false;
                }

                $durationHours = (float) ($booking->ThoiLuongGio ?? 0);
                if ($durationHours < 2) {
                    return false;
                }

                $start = Carbon::createFromFormat('H:i:s', $session->GioBatDau);
                $sessionStart = Carbon::parse($session->NgayLam)
                    ->setTime($start->hour, $start->minute, $start->second);

                return $sessionStart->gte($nowPlus2h) && $sessionStart->lte($sevenDaysAhead);
            })
            ->map(function (LichBuoiThang $session) {
                $booking = $session->donDat;
                $start = Carbon::createFromFormat('H:i:s', $session->GioBatDau);
                $end = $start->copy()->addHours((float) ($booking->ThoiLuongGio ?? 0));
                $kh = $booking?->khachHang;
                $address = $booking?->diaChi;

                return [
                    'id' => $session->ID_Buoi,
                    'item_type' => 'month_session',
                    'booking_id' => $session->ID_DD,
                    'service_id' => $booking?->ID_DV,
                    'work_date' => $session->NgayLam,
                    'start_time' => $session->GioBatDau ? substr($session->GioBatDau, 0, 5) : null,
                    'duration_hours' => (float) ($booking->ThoiLuongGio ?? 0),
                    'end_time' => $end->format('H:i'),
                    'status' => $session->TrangThaiBuoi,
                    'note' => $booking?->GhiChu,
                    'customer_name' => $kh?->Ten_KH,
                    'customer_phone' => $kh?->SDT,
                    'address' => $address ? [
                        'id' => $address->ID_DC,
                        'unit' => $address->CanHo,
                        'full_address' => $address->DiaChiDayDu,
                    ] : null,
                ];
            })
            ->values();

        $combined = collect($candidates->all())
            ->merge(collect($monthlySessions->all()))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $combined,
        ]);
    }

    /**
     * Monthly sessions waiting for staff
     * GET /api/staff/month-sessions/available
     */
    public function availableMonthSessions(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được buổi tháng.',
            ], 403);
        }

        $nowPlus2h = Carbon::now()->addHours(2);

        $sessions = LichBuoiThang::whereIn('TrangThaiBuoi', ['finding_staff', 'rejected'])
            ->whereNull('ID_NV')
            ->with(['donDat.khachHang', 'donDat.diaChi'])
            ->orderBy('NgayLam')
            ->get()
            ->filter(function (LichBuoiThang $session) use ($nowPlus2h) {
                $booking = $session->donDat;
                if (!$booking || !$session->NgayLam || !$session->GioBatDau) {
                    return false;
                }

                $durationHours = (float) ($booking->ThoiLuongGio ?? 0);
                if ($durationHours < 2) {
                    return false;
                }

                $start = Carbon::createFromFormat('H:i:s', $session->GioBatDau);
                $sessionStart = Carbon::parse($session->NgayLam)
                    ->setTime($start->hour, $start->minute, $start->second);

                return $sessionStart->gte($nowPlus2h);
            })
            ->map(function (LichBuoiThang $session) {
                $booking = $session->donDat;
                $start = Carbon::createFromFormat('H:i:s', $session->GioBatDau);
                $end = $start->copy()->addHours((float) ($booking->ThoiLuongGio ?? 0));
                $kh = $booking?->khachHang;
                $address = $booking?->diaChi;

                return [
                    'id' => $session->ID_Buoi,
                    'item_type' => 'month_session',
                    'booking_id' => $session->ID_DD,
                    'service_id' => $booking?->ID_DV,
                    'work_date' => $session->NgayLam,
                    'start_time' => $session->GioBatDau ? substr($session->GioBatDau, 0, 5) : null,
                    'duration_hours' => (float) ($booking->ThoiLuongGio ?? 0),
                    'end_time' => $end->format('H:i'),
                    'status' => $session->TrangThaiBuoi,
                    'note' => $booking?->GhiChu,
                    'customer_name' => $kh?->Ten_KH,
                    'customer_phone' => $kh?->SDT,
                    'address' => $address ? [
                        'id' => $address->ID_DC,
                        'unit' => $address->CanHo,
                        'full_address' => $address->DiaChiDayDu,
                    ] : null,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * List month sessions assigned to staff
     * GET /api/staff/month-sessions
     */
    public function monthSessions(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được buổi tháng.',
            ], 403);
        }

        $statusFilter = $request->query('status');
        $statuses = match ($statusFilter) {
            'rejected' => ['rejected'],
            'completed', 'done' => ['completed'],
            'cancelled' => ['cancelled'],
            'history' => ['completed', 'rejected', 'cancelled'],
            'all' => ['assigned', 'confirmed', 'completed', 'rejected', 'cancelled', 'finding_staff'],
            default => ['assigned', 'confirmed', 'completed'],
        };

        $sessions = LichBuoiThang::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiBuoi', $statuses)
            ->with(['donDat.khachHang', 'donDat.diaChi'])
            ->orderByDesc('NgayLam')
            ->get()
            ->filter(function (LichBuoiThang $session) {
                return $session->donDat && $session->donDat->LoaiDon === 'month';
            })
            ->map(function (LichBuoiThang $session) {
                $booking = $session->donDat;
                $durationHours = (float) ($booking?->ThoiLuongGio ?? 0);
                $startTime = $session->GioBatDau;
                $end = null;
                if ($startTime && $durationHours > 0) {
                    $end = Carbon::createFromFormat('H:i:s', $startTime)
                        ->addHours($durationHours)
                        ->format('H:i');
                }
                $kh = $booking?->khachHang;
                $address = $booking?->diaChi;

                return [
                    'id' => $session->ID_Buoi,
                    'item_type' => 'month_session',
                    'booking_id' => $session->ID_DD,
                    'work_date' => $session->NgayLam,
                    'start_time' => $startTime ? substr($startTime, 0, 5) : null,
                    'duration_hours' => $durationHours,
                    'end_time' => $end,
                    'status' => $session->TrangThaiBuoi,
                    'note' => $booking?->GhiChu,
                    'customer_name' => $kh?->Ten_KH,
                    'customer_phone' => $kh?->SDT,
                    'address' => $address ? [
                        'id' => $address->ID_DC,
                        'unit' => $address->CanHo,
                        'full_address' => $address->DiaChiDayDu,
                    ] : null,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Month session detail for staff
     * GET /api/staff/month-sessions/{id}
     */
    public function showMonthSession(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được buổi tháng.',
            ], 403);
        }

        $session = LichBuoiThang::where('ID_Buoi', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->with(['donDat.khachHang'])
            ->first();

        if (!$session || !$session->donDat || $session->donDat->LoaiDon !== 'month') {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy buổi tháng phù hợp.',
            ], 404);
        }

        $booking = $session->donDat;
        $service = DichVu::find($booking->ID_DV);
        $address = DiaChi::find($booking->ID_DC);
        $kh = $booking->khachHang;
        $end = null;
        $durationHours = (float) ($booking->ThoiLuongGio ?? 0);

        if ($session->GioBatDau && $durationHours > 0) {
            $end = Carbon::createFromFormat('H:i:s', $session->GioBatDau)
                ->addHours($durationHours)
                ->format('H:i');
        }

        $sessionCount = LichBuoiThang::where('ID_DD', $booking->ID_DD)->count();
        $sessionCount = max(1, $sessionCount);
        $totalAmount = (float) ($booking->TongTien ?? 0) / $sessionCount;
        $discountedAmount = (float) ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0) / $sessionCount;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->ID_DD,
                'session_id' => $session->ID_Buoi,
                'item_type' => 'month_session',
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
                'work_date' => $session->NgayLam,
                'start_time' => $session->GioBatDau ? substr($session->GioBatDau, 0, 5) : null,
                'duration_hours' => $durationHours,
                'end_time' => $end,
                'status' => $session->TrangThaiBuoi,
                'total_amount' => $totalAmount,
                'discounted_amount' => $discountedAmount,
                'created_at' => $booking->NgayTao,
                'customer' => [
                    'name' => $kh?->Ten_KH,
                    'phone' => $kh?->SDT,
                ],
            ],
        ]);
    }

    /**
     * Thu thap nhan vien theo don hoan thanh
     * GET /api/staff/earnings
     * Lay du lieu tu LichSuViNhanVien (giao dich vi) thay vi DonDat
     */
    public function earnings(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được thu nhập.',
            ], 403);
        }

        $from = $request->query('from');
        $to = $request->query('to');
        $method = $request->query('method');

        // Lay cac giao dich lien quan den don hang tu vi nhan vien
        // order_credit = don VNPay thanh cong (cong 80%)
        // cash_commission = don tien mat (tru 20%)
        $query = \App\Models\LichSuViNhanVien::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('LoaiGiaoDich', ['order_credit', 'cash_commission'])
            ->where('TrangThai', 'success')
            ->whereNotNull('ID_DD');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Loc theo phuong thuc thanh toan
        if ($method === 'cash') {
            $query->where('LoaiGiaoDich', 'cash_commission');
        } elseif (in_array($method, ['wallet', 'online', 'vnpay'])) {
            $query->where('LoaiGiaoDich', 'order_credit');
        }

        $transactions = $query->orderByDesc('created_at')->get();

        $items = [];
        $total = 0.0;
        $totalCash = 0.0;
        $totalWallet = 0.0;

        foreach ($transactions as $tx) {
            $orderId = $tx->ID_DD;
            $sessionId = null;
            $workDate = null;
            $startTime = null;
            $durationHours = 0;
            
            // Kiem tra xem ID_DD co chua ID buoi thang khong (format: DD_xxx_LBT_yyy)
            if (preg_match('/^(.+)_(LBT_.+)$/', $orderId, $matches)) {
                $bookingId = $matches[1]; // DD_xxx
                $sessionId = $matches[2]; // LBT_yyy
                
                $booking = DonDat::find($bookingId);
                $session = LichBuoiThang::where('ID_Buoi', $sessionId)->first();
                
                if ($session) {
                    $workDate = $session->NgayLam;
                    $startTime = $session->GioBatDau ? substr($session->GioBatDau, 0, 5) : null;
                }
                $durationHours = (float) ($booking?->ThoiLuongGio ?? 0);
            } else {
                // Don theo gio binh thuong
                $booking = DonDat::find($orderId);
                $workDate = $booking?->NgayLam;
                $startTime = $booking?->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null;
                $durationHours = (float) ($booking?->ThoiLuongGio ?? 0);
            }
            
            // Tinh so tien thuc nhan tu don
            // order_credit = 80% (da cong vao vi)
            // cash_commission = -20% (da tru tu vi), tuc nhan vien nhan 80% tien mat
            $isCash = $tx->LoaiGiaoDich === 'cash_commission';
            
            // Tinh nguoc lai tong tien don tu giao dich vi
            // order_credit = 80% => tong don = SoTien / 0.8
            // cash_commission = -20% => tong don = |SoTien| / 0.2
            if ($isCash) {
                $orderAmount = abs((float) $tx->SoTien) / 0.2; // Tong tien don
                $netAmount = $orderAmount * 0.8; // Nhan vien nhan 80% tien mat
            } else {
                $netAmount = (float) $tx->SoTien; // 80% da duoc cong
                $orderAmount = $netAmount / 0.8; // Tong tien don
            }

            if ($isCash) {
                $totalCash += $orderAmount;
            } else {
                $totalWallet += $orderAmount;
            }
            $total += $orderAmount;

            $items[] = [
                'id' => $orderId,
                'work_date' => $workDate,
                'start_time' => $startTime,
                'duration_hours' => $durationHours,
                'amount' => round($orderAmount, 0),
                'net_amount' => round($netAmount, 0),
                'payment_method' => $isCash ? 'cash' : 'wallet',
                'payment_status' => 'ThanhCong',
                'status' => 'completed',
                'transaction_id' => $tx->ID_LSV,
                'transaction_date' => $tx->created_at?->toDateTimeString(),
                'is_month_session' => $sessionId !== null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => round($total, 0),
                'total_cash' => round($totalCash, 0),
                'total_wallet' => round($totalWallet, 0),
                'items' => $items,
            ],
        ]);
    }

    /**
     * Bao cao tuan cho nhan vien
     * GET /api/staff/weekly-report?start=YYYY-MM-DD&end=YYYY-MM-DD
     * Lay du lieu tu LichSuViNhanVien (giao dich vi), loc theo ngay lam viec thuc te
     */
    public function weeklyReport(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được báo cáo.',
            ], 403);
        }

        // Cho phép nhân ca start/end hoac from/to, mặc định là tuần hiện tại (Thứ 2 - Chủ nhật)
        $startParam = $request->query('start') ?? $request->query('from');
        $endParam = $request->query('end') ?? $request->query('to');

        $startDate = $startParam
            ? Carbon::parse($startParam)->startOfDay()
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endDate = $endParam
            ? Carbon::parse($endParam)->endOfDay()
            : $startDate->copy()->endOfWeek(Carbon::SUNDAY);

        // Loc theo hinh thuc thanh toan neu duoc truyen tu UI (cash / wallet)
        $methodFilter = $request->query('method');
        $methodFilter = in_array($methodFilter, ['cash', 'wallet'], true) ? $methodFilter : null;

        // Lay tat ca giao dich lien quan den don hang tu vi nhan vien
        $query = \App\Models\LichSuViNhanVien::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('LoaiGiaoDich', ['order_credit', 'cash_commission'])
            ->where('TrangThai', 'success')
            ->whereNotNull('ID_DD');

        // Loc theo phuong thuc thanh toan
        if ($methodFilter === 'cash') {
            $query->where('LoaiGiaoDich', 'cash_commission');
        } elseif ($methodFilter === 'wallet') {
            $query->where('LoaiGiaoDich', 'order_credit');
        }

        $allTransactions = $query->get();

        $completedCount = 0;
        $incomeTotal = 0.0;
        $incomeCash = 0.0;
        $incomeOnline = 0.0;

        foreach ($allTransactions as $tx) {
            $orderId = $tx->ID_DD;
            $workDate = null;
            
            // Parse ID_DD de lay ngay lam viec thuc te
            if (preg_match('/^(.+)_(LBT_.+)$/', $orderId, $matches)) {
                // Don thang: format DD_xxx_LBT_yyy
                $sessionId = $matches[2];
                $session = LichBuoiThang::where('ID_Buoi', $sessionId)->first();
                $workDate = $session?->NgayLam;
            } else {
                // Don theo gio binh thuong
                $booking = DonDat::find($orderId);
                $workDate = $booking?->NgayLam;
            }
            
            // Chi tinh neu ngay lam viec nam trong khoang thoi gian
            if (!$workDate) {
                continue;
            }
            
            $workDateCarbon = Carbon::parse($workDate);
            if ($workDateCarbon->lt($startDate) || $workDateCarbon->gt($endDate)) {
                continue;
            }
            
            $completedCount++;
            
            $isCash = $tx->LoaiGiaoDich === 'cash_commission';
            
            // Tinh nguoc lai tong tien don tu giao dich vi
            if ($isCash) {
                $orderAmount = abs((float) $tx->SoTien) / 0.2;
                $incomeCash += $orderAmount;
            } else {
                $netAmount = (float) $tx->SoTien;
                $orderAmount = $netAmount / 0.8;
                $incomeOnline += $orderAmount;
            }
            
            $incomeTotal += $orderAmount;
        }

        // Danh gia trong tuan
        $ratings = DanhGiaNhanVien::where('ID_NV', $nhanVien->ID_NV)
            ->whereBetween('ThoiGian', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay(),
            ])
            ->get();

        $ratingCount = $ratings->count();
        $ratingSum = $ratings->sum(function ($r) {
            return (float) ($r->Diem ?? 0);
        });
        $ratingAvg = $ratingCount > 0 ? round($ratingSum / $ratingCount, 2) : 0.0;

        $breakdown = [
            1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0,
        ];
        foreach ($ratings as $rating) {
            $score = (int) floor((float) ($rating->Diem ?? 0));
            $score = max(1, min(5, $score));
            $breakdown[$score]++;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'completed_jobs' => $completedCount,
                'income_total' => round($incomeTotal, 0),
                'income_cash' => round($incomeCash, 0),
                'income_online' => round($incomeOnline, 0),
                'rating_avg' => $ratingAvg,
                'rating_count' => $ratingCount,
                'rating_breakdown' => $breakdown,
            ],
        ]);
    }

    /**
     * List bookings assigned to the staff (default: assigned + completed)
     * GET /api/staff/bookings
     */
    public function index(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được danh sách đơn.',
            ], 403);
        }

        $statusFilter = $request->query('status');
        $statuses = match ($statusFilter) {
            'rejected' => ['rejected'],
            'completed', 'done' => ['completed', 'done'],
            'cancelled' => ['cancelled'],
            'history' => ['completed', 'done', 'rejected', 'cancelled'],
            'all' => ['assigned', 'confirmed', 'completed', 'rejected', 'done', 'cancelled', 'finding_staff'],
            default => ['assigned', 'confirmed', 'completed'],
        };

        $bookings = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiDon', $statuses)
            ->with('lichBuoiThang')
            ->orderByDesc('NgayLam')
            ->get()
            ->map(function (DonDat $booking) use ($nhanVien) {
                $status = $booking->TrangThaiDon;
                if (in_array($status, ['done', 'completed'], true)) {
                    $status = 'completed';
                } elseif ($status === 'cancelled') {
                    $status = 'cancelled';
                }

                $sessionDate = $booking->NgayLam;
                $sessionStart = $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null;

                if ($booking->LoaiDon === 'month') {
                    $sessions = $booking->lichBuoiThang;
                    if ($sessions) {
                        $targetSession = $sessions
                            ->filter(function ($s) use ($nhanVien) {
                                return ($s->ID_NV === $nhanVien->ID_NV) &&
                                    in_array($s->TrangThaiBuoi, ['confirmed', 'completed'], true);
                            })
                            ->sortBy(['NgayLam', 'GioBatDau'])
                            ->first();

                        if (!$targetSession) {
                            $targetSession = $sessions->sortBy(['NgayLam', 'GioBatDau'])->first();
                        }

                        if ($targetSession && $targetSession->NgayLam && $targetSession->GioBatDau) {
                            $sessionDate = $targetSession->NgayLam;
                            $sessionStart = substr($targetSession->GioBatDau, 0, 5);
                        }
                    }
                }

                return [
                    'id' => $booking->ID_DD,
                    'service_id' => $booking->ID_DV,
                    'work_date' => $sessionDate,
                    'start_time' => $sessionStart,
                    'duration_hours' => (float) $booking->ThoiLuongGio,
                    'status' => $status,
                    'note' => $booking->GhiChu,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    /**
     * Booking detail for staff
     * GET /api/staff/bookings/{id}
     */
    public function show(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được đơn.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->with(['khachHang', 'lichBuoiThang'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn.',
            ], 404);
        }

        $service = DichVu::find($booking->ID_DV);
        $address = DiaChi::find($booking->ID_DC);
        $kh = $booking->khachHang;
        $end = null;
        $sessionId = null;

        $workDate = $booking->NgayLam;
        $startTime = $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null;
        $totalAmount = (float) $booking->TongTien;
        $discountedAmount = (float) ($booking->TongTienSauGiam ?? $booking->TongTien);
        $sessionCount = 1;

        if ($booking->LoaiDon === 'month') {
            $sessions = $booking->lichBuoiThang ?? collect();

            $targetSession = $sessions
                ->filter(function ($s) use ($nhanVien) {
                    return ($s->ID_NV === $nhanVien->ID_NV) &&
                        in_array($s->TrangThaiBuoi, ['confirmed', 'completed'], true);
                })
                ->sortBy([
                    ['NgayLam', 'asc'],
                    ['GioBatDau', 'asc'],
                ])
                ->first();

            if (!$targetSession && $sessions->isNotEmpty()) {
                $targetSession = $sessions
                    ->sortBy([
                        ['NgayLam', 'asc'],
                        ['GioBatDau', 'asc'],
                    ])
                    ->first();
            }

            if ($targetSession) {
                $sessionId = $targetSession->ID_Buoi;
                $workDate = $targetSession->NgayLam;
                $startTime = $targetSession->GioBatDau
                    ? substr($targetSession->GioBatDau, 0, 5)
                    : $startTime;
                if ($targetSession->GioBatDau && $booking->ThoiLuongGio) {
                    $endTime = Carbon::createFromFormat('H:i:s', $targetSession->GioBatDau)
                        ->addHours((float) $booking->ThoiLuongGio);
                    $end = $endTime->format('H:i');
                }
            }
            // tinh so buoi de chia tien theo buoi
            $sessionCount = max(1, $sessions->count());
            $totalAmount = $totalAmount / $sessionCount;
            $discountedAmount = $discountedAmount / $sessionCount;
        } else {
            if ($booking->GioBatDau && $booking->ThoiLuongGio) {
                $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
                $end = $start->copy()->addHours((float) $booking->ThoiLuongGio)->format('H:i');
            }
        }

        $status = $booking->TrangThaiDon;
        if ($status === 'done') {
            $status = 'completed';
        } elseif ($status === 'cancelled') {
            $status = 'cancelled';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->ID_DD,
                'session_id' => $sessionId,
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
                'work_date' => $workDate,
                'start_time' => $startTime,
                'duration_hours' => (float) $booking->ThoiLuongGio,
                'end_time' => $end,
                'status' => $status,
                'total_amount' => $totalAmount,
                'discounted_amount' => $discountedAmount,
                'created_at' => $booking->NgayTao,
                'customer' => [
                    'name' => $kh?->Ten_KH,
                    'phone' => $kh?->SDT,
                ],
            ],
        ]);
    }

    /**
     * Staff accepts an assigned booking -> confirmed
     * POST /api/staff/bookings/{id}/confirm
     */
    public function confirm(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới nhận được đơn.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn.',
            ], 404);
        }

        $walletService = app(StaffWalletService::class);
        if ($response = $this->blockIfWalletLow($nhanVien, $walletService)) {
            return $response;
        }

        if ($booking->TrangThaiDon !== 'assigned') {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ được nhận đơn khi trạng thái là assigned.',
            ], 422);
        }

        if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
            return response()->json([
                'success' => false,
                'error' => 'Đơn thiếu thông tin thời gian.',
            ], 422);
        }

        $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
        $end = $start->copy()->addHours((float) $booking->ThoiLuongGio);

        $busy = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->where('ID_DD', '!=', $id)
            ->whereIn('TrangThaiDon', ['assigned', 'confirmed'])
            ->get()
            ->map(function (DonDat $b) {
                if (!$b->NgayLam || !$b->GioBatDau || !$b->ThoiLuongGio) {
                    return null;
                }
                $startBusy = Carbon::createFromFormat('H:i:s', $b->GioBatDau);
                $endBusy = $startBusy->copy()->addHours((float) $b->ThoiLuongGio);
                return [
                    'date' => $b->NgayLam,
                    'start' => $startBusy,
                    'end' => $endBusy,
                ];
            })
            ->filter()
            ->values();

        foreach ($busy as $b) {
            if ($b['date'] !== $booking->NgayLam) {
                continue;
            }
            $gapEnd = $b['end']->copy()->addHour();
            if ($this->overlaps($start, $end, $b['start'], $b['end'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Thời gian đơn này trùng với đơn bạn đang nhận.',
                ], 422);
            }
            if ($start->gte($b['start']) && $start->lt($gapEnd)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cần cách ít nhất 1 giờ sau khi kết thúc đơn trước.',
                ], 422);
            }
        }

        $oldStatus = $booking->TrangThaiDon;
        // Staff confirms they will do the job
        $booking->TrangThaiDon = 'confirmed';
        $booking->save();

        // Send notification to customer
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyOrderStatusChanged($booking, $oldStatus, 'confirmed');
        } catch (\Exception $e) {
            Log::error('Failed to send status change notification', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        // Mark the matching schedule as assigned so it is counted
        $this->touchScheduleStatus($nhanVien->ID_NV, $booking, 'assigned');

        return response()->json([
            'success' => true,
            'message' => 'Đã nhận đơn.',
        ]);
    }

    /**
     * Staff claims an available booking (finding_staff / rejected)
     * POST /api/staff/bookings/{id}/claim
     */
    public function claim(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới nhận được đơn.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->whereIn('TrangThaiDon', ['finding_staff', 'rejected'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn phù hợp.',
            ], 404);
        }

        $walletService = app(StaffWalletService::class);
        if ($response = $this->blockIfWalletLow($nhanVien, $walletService)) {
            return $response;
        }

        if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
            return response()->json([
                'success' => false,
                'error' => 'Đơn thiếu thông tin thời gian.',
            ], 422);
        }

        if ((float) $booking->ThoiLuongGio < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhận đơn có thời lượng từ 2 giờ trở lên.',
            ], 422);
        }

        $startTime = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
        $bookingStart = Carbon::parse($booking->NgayLam)
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);
        $nowPlus2h = Carbon::now()->addHours(2);
        $nowPlus7d = Carbon::now()->addDays(7)->endOfDay();
        if ($bookingStart->lt($nowPlus2h)) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhận đơn bắt đầu sau ít nhất 2 giờ.',
            ], 422);
        }

        if ($bookingStart->gt($nowPlus7d)) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhận đơn trong vòng 7 ngày tới.',
            ], 422);
        }

        $busy = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiDon', ['assigned', 'confirmed'])
            ->get()
            ->map(function (DonDat $b) {
                if (!$b->NgayLam || !$b->GioBatDau || !$b->ThoiLuongGio) {
                    return null;
                }
                $start = Carbon::createFromFormat('H:i:s', $b->GioBatDau);
                $end = $start->copy()->addHours((float) $b->ThoiLuongGio);
                return [
                    'date' => $b->NgayLam,
                    'start' => $start,
                    'end' => $end,
                ];
            })
            ->filter()
            ->values();

        $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
        $end = $start->copy()->addHours((float) $booking->ThoiLuongGio);

        foreach ($busy as $b) {
            if ($b['date'] === $booking->NgayLam && $this->overlaps($start, $end, $b['start'], $b['end'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Thời gian đơn này trùng với đơn bạn đang nhận.',
                ], 422);
            }

            if ($b['date'] === $booking->NgayLam) {
                $gapEnd = $b['end']->copy()->addHour();
                if ($start->gte($b['start']) && $start->lt($gapEnd)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cần cách ít nhất 1 giờ sau khi kết thúc đơn trước.',
                    ], 422);
                }
            }
        }

        $oldStatus = $booking->TrangThaiDon;
        $booking->ID_NV = $nhanVien->ID_NV;
        $booking->TrangThaiDon = 'confirmed'; // nhan va xac nhan lam viec
        $booking->save();

        // Send notification to customer
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyOrderStatusChanged($booking, $oldStatus, 'confirmed');
        } catch (\Exception $e) {
            Log::error('Failed to send status change notification', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        // Mark matching schedule as assigned if exists
        $this->touchScheduleStatus($nhanVien->ID_NV, $booking, 'assigned');

        return response()->json([
            'success' => true,
            'message' => 'Đã nhận và xác nhận đơn.',
        ]);
    }

    /**
     * Staff claims an available monthly session
     * POST /api/staff/month-sessions/{id}/claim
     */
    public function claimMonthSession(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới nhận được buổi tháng.',
            ], 403);
        }

        $session = LichBuoiThang::where('ID_Buoi', $id)
            ->whereIn('TrangThaiBuoi', ['finding_staff', 'rejected'])
            ->whereNull('ID_NV')
            ->with('donDat')
            ->first();

        if (!$session || !$session->donDat || $session->donDat->LoaiDon !== 'month') {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy buổi tháng phù hợp.',
            ], 404);
        }

        if (!$session->NgayLam || !$session->GioBatDau) {
            return response()->json([
                'success' => false,
                'error' => 'Buổi tháng thiếu thông tin thời gian.',
            ], 422);
        }

        $durationHours = (float) ($session->donDat->ThoiLuongGio ?? 0);
        if ($durationHours < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhận buổi từ 2 giờ trở lên.',
            ], 422);
        }

        $start = Carbon::createFromFormat('H:i:s', $session->GioBatDau);
        $sessionStart = Carbon::parse($session->NgayLam)
            ->setTime($start->hour, $start->minute, $start->second);

        if ($sessionStart->lt(Carbon::now()->addHours(2))) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhận buổi bắt đầu sau ít nhất 2 giờ.',
            ], 422);
        }

        $booking = $session->donDat;
        $duration = (float) ($booking->ThoiLuongGio ?? 0);
        $startTime = Carbon::createFromFormat('H:i:s', $session->GioBatDau);
        $endTime = $startTime->copy()->addHours($duration);
        $sevenDaysAhead = Carbon::now()->addDays(7)->endOfDay();

        $sessionStart = Carbon::parse($session->NgayLam)
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);

        if ($sessionStart->gt($sevenDaysAhead)) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhận buổi trong vòng 7 ngày tới.',
            ], 422);
        }

        $busyBookings = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiDon', ['assigned', 'confirmed'])
            ->get()
            ->map(function (DonDat $b) {
                if (!$b->NgayLam || !$b->GioBatDau || !$b->ThoiLuongGio) {
                    return null;
                }
                $startBusy = Carbon::createFromFormat('H:i:s', $b->GioBatDau);
                $endBusy = $startBusy->copy()->addHours((float) $b->ThoiLuongGio);
                return [
                    'date' => $b->NgayLam,
                    'start' => $startBusy,
                    'end' => $endBusy,
                ];
            })
            ->filter()
            ->values();

        $busySessions = LichBuoiThang::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiBuoi', ['assigned', 'confirmed', 'completed'])
            ->with('donDat')
            ->get()
            ->map(function (LichBuoiThang $s) {
                if (!$s->NgayLam || !$s->GioBatDau || !$s->donDat) {
                    return null;
                }
                $startS = Carbon::createFromFormat('H:i:s', $s->GioBatDau);
                $endS = $startS->copy()->addHours((float) ($s->donDat->ThoiLuongGio ?? 0));
                return [
                    'date' => $s->NgayLam,
                    'start' => $startS,
                    'end' => $endS,
                ];
            })
            ->filter()
            ->values();

        foreach ($busyBookings as $b) {
            if ($b['date'] !== $session->NgayLam) {
                continue;
            }
            $gapEnd = $b['end']->copy()->addHour();
            if ($this->overlaps($startTime, $endTime, $b['start'], $b['end'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Thời gian buổi này trùng với đơn bạn đang nhận.',
                ], 422);
            }
            if ($startTime->gte($b['start']) && $startTime->lt($gapEnd)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cần cách ít nhất 1 giờ sau khi kết thúc đơn trước.',
                ], 422);
            }
        }

        foreach ($busySessions as $b) {
            if ($b['date'] !== $session->NgayLam) {
                continue;
            }
            $gapEnd = $b['end']->copy()->addHour();
            if ($this->overlaps($startTime, $endTime, $b['start'], $b['end'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Thời gian buổi này trùng với buổi tháng bạn đang nhận.',
                ], 422);
            }
            if ($startTime->gte($b['start']) && $startTime->lt($gapEnd)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cần cách ít nhất 1 giờ sau khi kết thúc buổi trước.',
                ], 422);
            }
        }

        $session->ID_NV = $nhanVien->ID_NV;
        $session->TrangThaiBuoi = 'confirmed';
        $session->save();

        // Gan nhan vien cho don thang neu chua co
        if (!$booking->ID_NV) {
            $booking->ID_NV = $nhanVien->ID_NV;
            $booking->TrangThaiDon = 'confirmed';
            $booking->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã nhận và xác nhận buổi tháng.',
        ]);
    }

    /**
     * Staff confirms an assigned monthly session -> confirmed
     * POST /api/staff/month-sessions/{id}/confirm
     */
    public function confirmMonthSession(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xác nhận được buổi tháng.',
            ], 403);
        }

        $session = LichBuoiThang::where('ID_Buoi', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->with('donDat')
            ->first();

        if (!$session || !$session->donDat || $session->donDat->LoaiDon !== 'month') {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy buổi tháng phù hợp.',
            ], 404);
        }

        if ($session->TrangThaiBuoi !== 'assigned') {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ xác nhận khi trạng thái là assigned.',
            ], 422);
        }

        if (!$session->NgayLam || !$session->GioBatDau) {
            return response()->json([
                'success' => false,
                'error' => 'Buổi tháng thiếu thông tin thời gian.',
            ], 422);
        }

        $session->TrangThaiBuoi = 'confirmed';
        $session->save();

        $booking = $session->donDat;
        if ($booking) {
            if (!$booking->ID_NV) {
                $booking->ID_NV = $nhanVien->ID_NV;
            }
            if (in_array($booking->TrangThaiDon, ['finding_staff', 'assigned'], true)) {
                $booking->TrangThaiDon = 'confirmed';
            }
            $booking->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xác nhận buổi tháng.',
        ]);
    }

    /**
     * Staff rejects an assigned monthly session -> rejected
     * POST /api/staff/month-sessions/{id}/reject
     */
    public function rejectMonthSession(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới từ chối được buổi tháng.',
            ], 403);
        }

        $session = LichBuoiThang::where('ID_Buoi', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->with('donDat')
            ->first();

        if (!$session || !$session->donDat || $session->donDat->LoaiDon !== 'month') {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy buổi tháng phù hợp.',
            ], 404);
        }

        if ($session->TrangThaiBuoi !== 'assigned') {
            return response()->json([
                'success' => false,
                'error' => 'Chi từ chối khi trạng thái buổi là assigned.',
            ], 422);
        }

        $booking = $session->donDat;

        $session->TrangThaiBuoi = 'rejected';
        $session->ID_NV = null;
        $session->save();

        if ($booking && $booking->ID_NV === $nhanVien->ID_NV) {
            $otherActiveSessions = $booking->lichBuoiThang()
                ->where('ID_Buoi', '<>', $session->ID_Buoi)
                ->where('ID_NV', $nhanVien->ID_NV)
                ->whereIn('TrangThaiBuoi', ['assigned', 'confirmed', 'completed'])
                ->count();

            if ($otherActiveSessions === 0) {
                $booking->ID_NV = null;
                if (in_array($booking->TrangThaiDon, ['assigned', 'confirmed'], true)) {
                    $booking->TrangThaiDon = 'finding_staff';
                }
                $booking->save();
            }
        }

        $this->logReject($nhanVien->ID_TK, $session->ID_Buoi, 'month_session');
        $locked = $this->checkAndBanStaff($nhanVien->ID_TK);

        return response()->json([
            'success' => true,
            'message' => $locked
                ? 'Đã từ chối buổi tháng và tài khoản bị khóa (banned) do từ chối quá 2 lần/tuan.'
                : 'Đã từ chối buổi tháng.',
            'locked' => $locked,
        ]);
    }

    /**
     * Staff marks a monthly session as completed
     * When this is the last actual (non-cancelled) session, process pending refunds for cancelled sessions
     * POST /api/staff/month-sessions/{id}/complete
     */
    public function completeMonthSession(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới hoàn thành được buổi.',
            ], 403);
        }

        $session = LichBuoiThang::where('ID_Buoi', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->with('donDat')
            ->first();

        if (!$session || !$session->donDat || $session->donDat->LoaiDon !== 'month') {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy buổi tháng phù hợp.',
            ], 404);
        }

        if (!in_array($session->TrangThaiBuoi, ['assigned', 'confirmed'], true)) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ hoàn thành buổi đang làm (assigned/confirmed).',
            ], 422);
        }

        $booking = $session->donDat;

        // Verify the session time is passed
        if ($session->NgayLam && $session->GioBatDau) {
            $durationHours = (float) ($booking->ThoiLuongGio ?? 2);
            $start = Carbon::createFromFormat('H:i:s', $session->GioBatDau);
            $sessionEnd = Carbon::parse($session->NgayLam)
                ->setTime($start->hour, $start->minute, $start->second)
                ->addHours($durationHours);

            if (Carbon::now()->lt($sessionEnd)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Chỉ hoàn thành sau khi kết thúc buổi.',
                ], 422);
            }
        }

        // Mark session as completed
        $session->TrangThaiBuoi = 'completed';
        $session->save();

        // Calculate wallet credit for this session
        $orderAmount = (float) ($booking->TongTien ?? $booking->TongTienSauGiam ?? 0);
        $sessionCount = LichBuoiThang::where('ID_DD', $booking->ID_DD)->count();
        $sessionCount = max(1, $sessionCount);
        $sessionAmount = $orderAmount / $sessionCount;

        if ($sessionAmount > 0) {
            $walletService = app(StaffWalletService::class);
            
            // Find payment method
            $payment = LichSuThanhToan::where('ID_DD', $booking->ID_DD)
                ->where('TrangThai', 'ThanhCong')
                ->where('LoaiGiaoDich', 'payment')
                ->first();
            
            $paymentMethod = $payment?->PhuongThucThanhToan ?? 'TienMat';
            $normalizedMethod = $paymentMethod === 'TienMat' ? 'cash' : 'online';

            // Use session ID to track to avoid duplicate credits
            $transactionRef = $booking->ID_DD . '_' . $session->ID_Buoi;
            
            if (!$walletService->hasOrderTransaction($transactionRef, ['cash_commission', 'order_credit'])) {
                if ($normalizedMethod === 'cash') {
                    $commission = -1 * round($sessionAmount * 0.2, 2);
                    $walletService->applyChange($nhanVien, $commission, 'cash_commission', [
                        'description' => 'Trừ 20% buổi tiền mặt ' . $session->ID_Buoi,
                        'order_id' => $transactionRef,
                        'source' => 'cash',
                    ]);
                } else {
                    $credit = round($sessionAmount * 0.8, 2);
                    $walletService->applyChange($nhanVien, $credit, 'order_credit', [
                        'description' => 'Cộng 80% buổi thanh toán online ' . $session->ID_Buoi,
                        'order_id' => $transactionRef,
                        'source' => strtolower((string) $paymentMethod),
                    ]);
                }
            }
        }

        // Check if this is the last actual (non-cancelled) session
        $allSessions = LichBuoiThang::where('ID_DD', $booking->ID_DD)->get();
        $remainingActiveSessions = $allSessions
            ->whereNotIn('TrangThaiBuoi', ['cancelled', 'completed'])
            ->count();

        $refundProcessed = false;
        $refundResult = null;

        if ($remainingActiveSessions === 0) {
            // This was the last actual session -> Process pending refunds for cancelled sessions
            Log::info('Last actual session completed, processing pending refunds', [
                'booking_id' => $booking->ID_DD,
                'session_id' => $session->ID_Buoi,
            ]);

            $refundService = app(RefundService::class);
            $refundResult = $refundService->processPendingRefunds($booking, 'last_session_completed');
            $refundProcessed = true;

            // Update order status to completed/done
            $booking->TrangThaiDon = 'completed';
            $booking->save();

            // Notify customer about completion and refund
            try {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyOrderStatusChanged($booking, 'confirmed', 'done');
                
                // If there were cancelled sessions and refund was processed
                if ($refundResult && $refundResult['cancelled_sessions'] > 0 && $refundResult['amount'] > 0) {
                    // Send additional notification about refund
                    Log::info('Pending refunds processed', [
                        'booking_id' => $booking->ID_DD,
                        'refund_amount' => $refundResult['amount'],
                        'cancelled_sessions' => $refundResult['cancelled_sessions'],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send completion notification', [
                    'booking_id' => $booking->ID_DD,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = 'Đã hoàn thành buổi làm.';
        if ($refundProcessed && $refundResult && $refundResult['cancelled_sessions'] > 0) {
            $message .= ' Đã hoàn tiền ' . number_format($refundResult['amount']) . 'đ cho ' . $refundResult['cancelled_sessions'] . ' buổi đã huỷ trước đó.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_last_session' => $remainingActiveSessions === 0,
            'refund_processed' => $refundProcessed,
            'refund_amount' => $refundResult['amount'] ?? 0,
        ]);
    }

    /**
     * Staff rejects an assigned booking -> rejected
     * POST /api/staff/bookings/{id}/reject
     */
    public function reject(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới từ chối được đơn.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn.',
            ], 404);
        }

        if ($booking->TrangThaiDon !== 'assigned') {
            return response()->json([
                'success' => false,
                'error' => 'Chi được từ chối đơn khi trạng thái là assigned.',
            ], 422);
        }

        $oldStatus = $booking->TrangThaiDon;
        $booking->TrangThaiDon = 'rejected';
        $booking->save();

        // Send notification to customer
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyOrderStatusChanged($booking, $oldStatus, 'rejected');
        } catch (\Exception $e) {
            Log::error('Failed to send status change notification', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        // Ensure schedule stays ready (not counted as assigned)
        $this->touchScheduleStatus($nhanVien->ID_NV, $booking, 'ready');

        $this->logReject($nhanVien->ID_TK, $booking->ID_DD, 'hour_booking');
        $locked = $this->checkAndBanStaff($nhanVien->ID_TK);

        return response()->json([
            'success' => true,
            'message' => $locked
                ? 'Đã từ chối đơn và tài khoản bị khóa (banned) do từ chối quá 2 lần/tuan.'
                : 'Đã từ chối đơn.',
            'locked' => $locked,
        ]);
    }

    /**
     * Staff marks booking as completed (after end time)
     * POST /api/staff/bookings/{id}/complete
     */
    public function complete(Request $request, string $id)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới hoàn thành được đơn.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy đơn.',
            ], 404);
        }

        if (!in_array($booking->TrangThaiDon, ['assigned', 'confirmed', 'completed'], true)) {
            return response()->json([
                'success' => false,
                'error' => 'Chi hoàn thành đơn đang làm.',
            ], 422);
        }

        if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
            return response()->json([
                'success' => false,
                'error' => 'Đơn thiếu thông tin thời gian.',
            ], 422);
        }

        $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
        $end = Carbon::parse($booking->NgayLam)
            ->setTime($start->hour, $start->minute, $start->second)
            ->addHours((float) $booking->ThoiLuongGio);

        if (Carbon::now()->lt($end)) {
            return response()->json([
                'success' => false,
                'error' => 'Chi hoàn thành sau khi kết thúc đơn.',
            ], 422);
        }

        $oldStatus = $booking->TrangThaiDon;
        // Chuyển sang trạng thái completed để đồng bộ với front; khách đánh giá xong sẽ về done.
        $booking->TrangThaiDon = 'completed';
        $booking->save();

        // Send notification to customer
        try {
            $notificationService = app(NotificationService::class);
            $notificationService->notifyOrderStatusChanged($booking, $oldStatus, 'done');
        } catch (\Exception $e) {
            Log::error('Failed to send status change notification', [
                'booking_id' => $booking->ID_DD,
                'error' => $e->getMessage(),
            ]);
        }

        // Cong so du neu thanh toan online (VNPay) thanh cong
        $payment = LichSuThanhToan::where('ID_DD', $booking->ID_DD)
            ->where('TrangThai', 'ThanhCong')
            ->orderByDesc('ThoiGian')
            ->first();

        // Dung gia goc (truoc giam) lam co so tinh hoa hong/hoan tien vi.
        // Neu la don goi thang, chi tinh theo 1 buoi = TongTien / so buoi.
        $orderAmount = (float) ($booking->TongTien ?? $booking->TongTienSauGiam ?? 0);
        if ($booking->LoaiDon === 'month') {
            $sessionCount = LichBuoiThang::where('ID_DD', $booking->ID_DD)->count();
            $sessionCount = max(1, $sessionCount);
            $orderAmount = $orderAmount / $sessionCount;
        }

        if ($orderAmount > 0) {
            $walletService = app(StaffWalletService::class);
            $paymentMethod = $payment?->PhuongThucThanhToan ?? 'TienMat';
            $normalizedMethod = $paymentMethod === 'TienMat' ? 'cash' : 'online';

            if (!$walletService->hasOrderTransaction(
                $booking->ID_DD,
                ['cash_commission', 'order_credit']
            )) {
                if ($normalizedMethod === 'cash') {
                    $commission = -1 * round($orderAmount * 0.2, 2);
                    $walletService->applyChange($nhanVien, $commission, 'cash_commission', [
                        'description' => 'Trừ 20% đơn tiền mặt ' . $booking->ID_DD,
                        'order_id' => $booking->ID_DD,
                        'source' => 'cash',
                    ]);
                } else {
                    $credit = round($orderAmount * 0.8, 2);
                    $walletService->applyChange($nhanVien, $credit, 'order_credit', [
                        'description' => 'Cộng 80% đơn thanh toán online ' . $booking->ID_DD,
                        'order_id' => $booking->ID_DD,
                        'source' => strtolower((string) $paymentMethod),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã hoàn thành đơn.',
        ]);
    }

    private function blockIfWalletLow(NhanVien $nhanVien, StaffWalletService $walletService): ?JsonResponse
    {
        if ($walletService->canReceiveOrders($nhanVien)) {
            return null;
        }

        return response()->json([
            'success' => false,
            'error' => 'Số dư không đủ. Vui lòng nạp tối thiểu 400.000đ qua VNPAY để nhận đơn.',
        ], 403);
    }

    private function overlaps(Carbon $startA, Carbon $endA, Carbon $startB, Carbon $endB): bool
    {
        return $startA->lt($endB) && $endA->gt($startB);
    }

    private function touchScheduleStatus(string $staffId, DonDat $booking, string $status): void
    {
        if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
            return;
        }

        $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
        $end = $start->copy()->addHours((int) $booking->ThoiLuongGio);

        $schedule = LichLamViec::where('ID_NV', $staffId)
            ->where('NgayLam', $booking->NgayLam)
            ->where('GioBatDau', '<=', $start->format('H:i:s'))
            ->where('GioKetThuc', '>=', $end->format('H:i:s'))
            ->orderBy('GioBatDau')
            ->first();

        if ($schedule) {
            $schedule->TrangThai = $status;
            $schedule->save();
        }
    }

    private function logReject(string $accountId, string $targetId, string $type): void
    {
        try {
            DB::table('StaffRejectLogs')->insert([
                'staff_id' => $accountId,
                'target_id' => $targetId,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Log reject failed', [
                'staff_id' => $accountId,
                'target_id' => $targetId,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function checkAndBanStaff(string $accountId): bool
    {
        $weekAgo = Carbon::now()->subDays(7);
        $count = DB::table('StaffRejectLogs')
            ->where('staff_id', $accountId)
            ->where('created_at', '>=', $weekAgo)
            ->count();

        if ($count > 2) {
            $taiKhoan = TaiKhoan::find($accountId);
            if ($taiKhoan && $taiKhoan->TrangThaiTK !== 'banned') {
                $taiKhoan->TrangThaiTK = 'banned';
                $taiKhoan->save();
            }
            return true;
        }

        return false;
    }
}
