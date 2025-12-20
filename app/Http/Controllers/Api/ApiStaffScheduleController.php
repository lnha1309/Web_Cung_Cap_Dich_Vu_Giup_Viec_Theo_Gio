<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LichLamViec;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiStaffScheduleController extends Controller
{
    /**
     * Get schedules for current staff between dates (default: this week)
     * GET /api/staff/schedules
     */
    public function index(Request $request)
    {
        $taiKhoan = $request->user();
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới xem được lịch.',
            ], 403);
        }

        $start = $request->query('start_date');
        $end = $request->query('end_date');

        if ($start) {
            $startDate = Carbon::parse($start)->startOfDay();
        } else {
            $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY);
        }

        if ($end) {
            $endDate = Carbon::parse($end)->endOfDay();
        } else {
            $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
        }

        $schedules = LichLamViec::where('ID_NV', $nhanVien->ID_NV)
            ->whereBetween('NgayLam', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('NgayLam')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->ID_Lich,
                    'date' => $item->NgayLam,
                    'start_time' => substr($item->GioBatDau, 0, 5),
                    'end_time' => substr($item->GioKetThuc, 0, 5),
                    'status' => $item->TrangThai,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'schedules' => $schedules,
            ],
        ]);
    }

    /**
     * Update or create schedules for current staff (ready only)
     * PUT /api/staff/schedules
     */
    public function update(Request $request)
    {
        $taiKhoan = $request->user();
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới sửa được lịch.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'schedules' => ['required', 'array', 'min:4'],
            'schedules.*.date' => ['required', 'date'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $normalized = [];
        $dates = [];

        if ($this->shouldBlockCurrentWeekRegistration($nhanVien->ID_NV, $request->input('schedules', []))) {
            return response()->json([
                'success' => false,
                'error' => 'Đã qua hạn đăng ký lịch tuần này. Vui lòng liên hệ tổng đài.',
            ], 422);
        }

        foreach ($request->input('schedules', []) as $schedule) {
            $date = Carbon::parse($schedule['date']);
            $start = Carbon::createFromFormat('H:i', $schedule['start_time']);
            $end = Carbon::createFromFormat('H:i', $schedule['end_time']);

            $startMinutes = $start->hour * 60 + $start->minute;
            $endMinutes = $end->hour * 60 + $end->minute;

            if ($end->lessThanOrEqualTo($start)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
                ], 422);
            }

            if ($startMinutes < 7 * 60 || $endMinutes > 17 * 60 || ($endMinutes - $startMinutes) < 240) {
                return response()->json([
                    'success' => false,
                    'error' => 'Mỗi ca phải trong 07:00-17:00 và ít nhất 4 giờ.',
                ], 422);
            }

            $dateStr = $date->format('Y-m-d');
            $normalized[] = [
                'date' => $dateStr,
                'start' => $start->format('H:i:s'),
                'end' => $end->format('H:i:s'),
            ];
            $dates[] = $dateStr;
        }

        $dates = array_values(array_unique($dates));

        $lockedDays = LichLamViec::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('NgayLam', $dates)
            ->where('TrangThai', 'assigned')
            ->pluck('NgayLam')
            ->unique()
            ->values();

        if ($lockedDays->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'Không thể sửa ngày đã được nhận đơn: ' . $lockedDays->implode(', '),
            ], 422);
        }

        $results = [];

        DB::transaction(function () use (&$results, $normalized, $dates, $nhanVien) {
            // Remove existing ready slots for these dates before inserting the new ones
            LichLamViec::where('ID_NV', $nhanVien->ID_NV)
                ->whereIn('NgayLam', $dates)
                ->where('TrangThai', 'ready')
                ->delete();

            foreach ($normalized as $item) {
                $id = 'LL_' . $nhanVien->ID_NV . '_' . str_replace('-', '', $item['date']);

                $schedule = LichLamViec::updateOrCreate(
                    ['ID_Lich' => $id],
                    [
                        'ID_NV' => $nhanVien->ID_NV,
                        'NgayLam' => $item['date'],
                        'GioBatDau' => $item['start'],
                        'GioKetThuc' => $item['end'],
                        'TrangThai' => 'ready',
                    ]
                );

                $results[] = [
                    'id' => $schedule->ID_Lich,
                    'date' => $schedule->NgayLam,
                    'start_time' => substr($schedule->GioBatDau, 0, 5),
                    'end_time' => substr($schedule->GioKetThuc, 0, 5),
                    'status' => $schedule->TrangThai,
                ];
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật lịch làm việc.',
            'data' => [
                'staff_id' => $nhanVien->ID_NV,
                'schedules' => $results,
            ],
        ]);
    }

    /**
     * Create or update work schedules for authenticated staff
     * POST /api/staff/schedules
     */
    public function store(Request $request)
    {
        $taiKhoan = $request->user();
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ nhân viên mới được đăng ký lịch.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'schedules' => ['required', 'array', 'min:4'],
            'schedules.*.date' => ['required', 'date'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $input = $request->input('schedules', []);
        if (count($input) < 5) {
            return response()->json([
                'success' => false,
                'error' => 'Chọn ít nhất 5 ngày trong tuần.',
            ], 422);
        }

        $normalized = [];
        $dates = [];

        if ($this->shouldBlockCurrentWeekRegistration($nhanVien->ID_NV, $request->input('schedules', []))) {
            return response()->json([
                'success' => false,
                'error' => 'Đã qua hạn đăng ký lịch tuần này. Vui lòng liên hệ tổng đài.',
            ], 422);
        }

        foreach ($input as $schedule) {
            $date = Carbon::parse($schedule['date']);
            $start = Carbon::createFromFormat('H:i', $schedule['start_time']);
            $end = Carbon::createFromFormat('H:i', $schedule['end_time']);

            $startMinutes = $start->hour * 60 + $start->minute;
            $endMinutes = $end->hour * 60 + $end->minute;

            if ($end->lessThanOrEqualTo($start)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
                ], 422);
            }

            if ($startMinutes < 7 * 60 || $endMinutes > 17 * 60 || ($endMinutes - $startMinutes) < 240) {
                return response()->json([
                    'success' => false,
                    'error' => 'Mỗi ca phải trong 07:00-17:00 và ít nhất 4 giờ.',
                ], 422);
            }

            $dateStr = $date->format('Y-m-d');
            $normalized[] = [
                'date' => $dateStr,
                'start' => $start->format('H:i:s'),
                'end' => $end->format('H:i:s'),
            ];
            $dates[] = $dateStr;
        }

        $dates = array_values(array_unique($dates));

        $lockedDays = LichLamViec::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('NgayLam', $dates)
            ->where('TrangThai', 'assigned')
            ->pluck('NgayLam')
            ->unique()
            ->values();

        if ($lockedDays->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'Không thể sửa ngày đã được nhận đơn: ' . $lockedDays->implode(', '),
            ], 422);
        }

        $results = [];

        DB::transaction(function () use (&$results, $normalized, $dates, $nhanVien) {
            LichLamViec::where('ID_NV', $nhanVien->ID_NV)
                ->whereIn('NgayLam', $dates)
                ->where('TrangThai', 'ready')
                ->delete();

            foreach ($normalized as $item) {
                $id = 'LL_' . $nhanVien->ID_NV . '_' . str_replace('-', '', $item['date']);

                $schedule = LichLamViec::updateOrCreate(
                    ['ID_Lich' => $id],
                    [
                        'ID_NV' => $nhanVien->ID_NV,
                        'NgayLam' => $item['date'],
                        'GioBatDau' => $item['start'],
                        'GioKetThuc' => $item['end'],
                        'TrangThai' => 'ready',
                    ]
                );

                $results[] = [
                    'id' => $schedule->ID_Lich,
                    'date' => $schedule->NgayLam,
                    'start_time' => substr($schedule->GioBatDau, 0, 5),
                    'end_time' => substr($schedule->GioKetThuc, 0, 5),
                    'status' => $schedule->TrangThai,
                ];
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu lịch làm việc.',
            'data' => [
                'staff_id' => $nhanVien->ID_NV,
                'schedules' => $results,
            ],
        ]);
    }

    /**
     * Sau thu 5 neu tuan hien tai chua co lich thi chan khong cho dang ky tuan do.
     */
    private function shouldBlockCurrentWeekRegistration(string $staffId, array $requestedSchedules): bool
    {
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $cutoff = $weekStart->copy()->addDays(3)->startOfDay(); // Thursday 00:00

        if ($now->lt($cutoff)) {
            return false;
        }

        // Da co lich tuan nay thi khong chan
        $hasCurrentWeekSchedule = LichLamViec::where('ID_NV', $staffId)
            ->whereBetween('NgayLam', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->exists();
        if ($hasCurrentWeekSchedule) {
            return false;
        }

        // Neu request co ngay nam trong tuan nay thi chan
        foreach ($requestedSchedules as $item) {
            if (!isset($item['date'])) {
                continue;
            }
            $date = Carbon::parse($item['date']);
            if ($date->between($weekStart, $weekEnd, true)) {
                return true;
            }
        }

        return false;
    }
}
