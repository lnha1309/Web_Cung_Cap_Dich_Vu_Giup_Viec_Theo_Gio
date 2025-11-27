<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\DonDat;
use App\Models\LichSuThanhToan;
use App\Models\LichLamViec;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Models\DanhGiaNhanVien;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
                'error' => 'Chi nhan vien moi xem duoc don.',
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
            ->values();

        $nowPlus2h = Carbon::now()->addHours(2);

        $candidates = DonDat::whereIn('TrangThaiDon', ['finding_staff', 'rejected'])
            ->with(['khachHang', 'diaChi'])
            ->orderBy('NgayLam')
            ->get()
            ->filter(function (DonDat $booking) use ($busy, $nowPlus2h) {
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
                if ($bookingStart->lt($nowPlus2h)) {
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

        return response()->json([
            'success' => true,
            'data' => $candidates,
        ]);
    }

    /**
     * Thu thap nhan vien theo don hoan thanh
     * GET /api/staff/earnings
     */
    public function earnings(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi xem duoc thu thap.',
            ], 403);
        }

        $from = $request->query('from');
        $to = $request->query('to');
        $method = $request->query('method');

        $methodFilters = match ($method) {
            'cash' => ['TienMat'],
            'wallet', 'online', 'vnpay' => ['VNPay', 'Momo'],
            default => null,
        };

        $bookings = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->where('TrangThaiDon', 'done')
            ->when($from, fn($q) => $q->whereDate('NgayLam', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('NgayLam', '<=', $to))
            ->orderByDesc('NgayLam')
            ->get();

        $items = [];
        $total = 0.0;
        $totalCash = 0.0;
        $totalWallet = 0.0;

        foreach ($bookings as $booking) {
            $payment = LichSuThanhToan::where('ID_DD', $booking->ID_DD)
                ->orderByDesc('ThoiGian')
                ->first();

            if ($payment && $payment->TrangThai !== 'ThanhCong') {
                continue;
            }

            $paymentMethodRaw = $payment?->PhuongThucThanhToan;
            if ($methodFilters !== null && !in_array($paymentMethodRaw, $methodFilters, true)) {
                continue;
            }

            $normalizedMethod = match ($paymentMethodRaw) {
                'TienMat' => 'cash',
                'VNPay', 'Momo', 'Refund' => 'wallet',
                default => 'unknown',
            };

            $amount = (float) ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0);
            if ($normalizedMethod === 'cash') {
                $totalCash += $amount;
            } elseif ($normalizedMethod === 'wallet') {
                $totalWallet += $amount;
            }
            $total += $amount;

            $items[] = [
                'id' => $booking->ID_DD,
                'work_date' => $booking->NgayLam,
                'start_time' => $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null,
                'duration_hours' => (float) $booking->ThoiLuongGio,
                'amount' => $amount,
                'payment_method' => $normalizedMethod,
                'payment_status' => $payment?->TrangThai,
                'status' => 'completed',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => round($total, 2),
                'total_cash' => round($totalCash, 2),
                'total_wallet' => round($totalWallet, 2),
                'items' => $items,
            ],
        ]);
    }

    /**
     * Bao cao tuan cho nhan vien
     * GET /api/staff/weekly-report?start=YYYY-MM-DD&end=YYYY-MM-DD
     */
    public function weeklyReport(Request $request)
    {
        $nhanVien = $this->requireStaff($request);
        if (!$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi xem duoc bao cao.',
            ], 403);
        }

        // Cho phep nhan ca start/end hoac from/to, mac dinh la tuan hien tai (Thu 2 - Chu nhat)
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

        // Cong viec hoan thanh
        $bookings = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiDon', ['done', 'completed'])
            ->whereBetween('NgayLam', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $completedCount = $bookings->count();
        $incomeTotal = 0.0;
        $incomeCash = 0.0;
        $incomeOnline = 0.0;

        foreach ($bookings as $booking) {
            $amount = (float) ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0);

            $payment = LichSuThanhToan::where('ID_DD', $booking->ID_DD)
                ->where('TrangThai', 'ThanhCong')
                ->orderByDesc('ThoiGian')
                ->first();

            $normalizedMethod = null;
            if ($payment) {
                $method = $payment->PhuongThucThanhToan;
                if ($method === 'TienMat') {
                    $normalizedMethod = 'cash';
                    $incomeCash += $amount;
                } else {
                    // VNPay / Momo / others
                    $normalizedMethod = 'wallet';
                    $incomeOnline += $amount;
                }
            } else {
                // Khong co lich su thanh toan -> coi nhu tien mat
                $normalizedMethod = 'cash';
                $incomeCash += $amount;
            }

            // Neu UI yeu cau loc theo hinh thuc thanh toan thi bo qua cac don khong khop
            if ($methodFilter !== null && $normalizedMethod !== $methodFilter) {
                continue;
            }

            $incomeTotal += $amount;
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
                'income_total' => round($incomeTotal, 2),
                'income_cash' => round($incomeCash, 2),
                'income_online' => round($incomeOnline, 2),
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
                'error' => 'Chi nhan vien moi xem duoc danh sach don.',
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
            ->orderByDesc('NgayLam')
            ->get()
            ->map(function (DonDat $booking) {
                $status = $booking->TrangThaiDon;
                if (in_array($status, ['done', 'completed'], true)) {
                    $status = 'completed';
                } elseif ($status === 'cancelled') {
                    $status = 'cancelled';
                }

                return [
                    'id' => $booking->ID_DD,
                    'service_id' => $booking->ID_DV,
                    'work_date' => $booking->NgayLam,
                    'start_time' => $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null,
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
                'error' => 'Chi nhan vien moi xem duoc don.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->with('khachHang')
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Khong tim thay don dat.',
            ], 404);
        }

        $service = DichVu::find($booking->ID_DV);
        $address = DiaChi::find($booking->ID_DC);
        $kh = $booking->khachHang;
        $end = null;
        if ($booking->GioBatDau && $booking->ThoiLuongGio) {
            $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
            $end = $start->copy()->addHours((float) $booking->ThoiLuongGio)->format('H:i');
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
                'end_time' => $end,
                'status' => $status,
                'total_amount' => (float) $booking->TongTien,
                'discounted_amount' => (float) $booking->TongTienSauGiam,
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
                'error' => 'Chi nhan vien moi nhan duoc don.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Khong tim thay don dat.',
            ], 404);
        }

        if ($booking->TrangThaiDon !== 'assigned') {
            return response()->json([
                'success' => false,
                'error' => 'Chi duoc nhan don khi trang thai la assigned.',
            ], 422);
        }

        if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
            return response()->json([
                'success' => false,
                'error' => 'Don thieu thong tin thoi gian.',
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
                    'error' => 'Thoi gian don nay trung voi don ban dang nhan.',
                ], 422);
            }
            if ($start->gte($b['start']) && $start->lt($gapEnd)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Can cach it nhat 1 gio sau khi ket thuc don truoc.',
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
            'message' => 'Da nhan don.',
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
                'error' => 'Chi nhan vien moi nhan duoc don.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->whereIn('TrangThaiDon', ['finding_staff', 'rejected'])
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Khong tim thay don phu hop.',
            ], 404);
        }

        if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
            return response()->json([
                'success' => false,
                'error' => 'Don thieu thong tin thoi gian.',
            ], 422);
        }

        if ((float) $booking->ThoiLuongGio < 2) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan don co thoi luong tu 2 gio tro len.',
            ], 422);
        }

        $startTime = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
        $bookingStart = Carbon::parse($booking->NgayLam)
            ->setTime($startTime->hour, $startTime->minute, $startTime->second);
        if ($bookingStart->lt(Carbon::now()->addHours(2))) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan don bat dau sau it nhat 2 gio.',
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
                    'error' => 'Thoi gian don nay trung voi don ban dang nhan.',
                ], 422);
            }

            if ($b['date'] === $booking->NgayLam) {
                $gapEnd = $b['end']->copy()->addHour();
                if ($start->gte($b['start']) && $start->lt($gapEnd)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Can cach it nhat 1 gio sau khi ket thuc don truoc.',
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
            'message' => 'Da nhan va xac nhan don.',
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
                'error' => 'Chi nhan vien moi tu choi duoc don.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Khong tim thay don dat.',
            ], 404);
        }

        if ($booking->TrangThaiDon !== 'assigned') {
            return response()->json([
                'success' => false,
                'error' => 'Chi duoc tu choi don khi trang thai la assigned.',
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

        return response()->json([
            'success' => true,
            'message' => 'Da tu choi don.',
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
                'error' => 'Chi nhan vien moi hoan thanh don.',
            ], 403);
        }

        $booking = DonDat::where('ID_DD', $id)
            ->where('ID_NV', $nhanVien->ID_NV)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Khong tim thay don dat.',
            ], 404);
        }

        if (!in_array($booking->TrangThaiDon, ['assigned', 'confirmed', 'completed'], true)) {
            return response()->json([
                'success' => false,
                'error' => 'Chi hoan thanh don dang lam.',
            ], 422);
        }

        if (!$booking->NgayLam || !$booking->GioBatDau || !$booking->ThoiLuongGio) {
            return response()->json([
                'success' => false,
                'error' => 'Don thieu thong tin thoi gian.',
            ], 422);
        }

        $start = Carbon::createFromFormat('H:i:s', $booking->GioBatDau);
        $end = Carbon::parse($booking->NgayLam)
            ->setTime($start->hour, $start->minute, $start->second)
            ->addHours((float) $booking->ThoiLuongGio);

        if (Carbon::now()->lt($end)) {
            return response()->json([
                'success' => false,
                'error' => 'Chi hoan thanh sau khi ket thuc don.',
            ], 422);
        }

        $oldStatus = $booking->TrangThaiDon;
        $booking->TrangThaiDon = 'done';
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

        if ($payment && $payment->PhuongThucThanhToan === 'VNPay') {
            $amount = (float) ($booking->TongTienSauGiam ?? $booking->TongTien ?? 0);
            if ($amount > 0) {
                $staff = NhanVien::where('ID_NV', $nhanVien->ID_NV)->first();
                if ($staff) {
                    $staff->SoDu = (float) ($staff->SoDu ?? 0) + $amount;
                    $staff->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Da hoan thanh don.',
        ]);
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
}
