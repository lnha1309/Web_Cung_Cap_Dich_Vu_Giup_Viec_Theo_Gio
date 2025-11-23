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
        $user = $request->user();
        $taiKhoan = $user?->taiKhoan;
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi xem duoc lich.',
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
        $user = $request->user();
        $taiKhoan = $user?->taiKhoan;
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi sua duoc lich.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'schedules' => ['required', 'array', 'min:1'],
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

        foreach ($request->input('schedules', []) as $schedule) {
            $date = Carbon::parse($schedule['date']);
            $start = Carbon::createFromFormat('H:i', $schedule['start_time']);
            $end = Carbon::createFromFormat('H:i', $schedule['end_time']);

            $startMinutes = $start->hour * 60 + $start->minute;
            $endMinutes = $end->hour * 60 + $end->minute;

            if ($end->lessThanOrEqualTo($start)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gio ket thuc phai lon hon gio bat dau.',
                ], 422);
            }

            if ($startMinutes < 7 * 60 || $endMinutes > 17 * 60 || ($endMinutes - $startMinutes) < 240) {
                return response()->json([
                    'success' => false,
                    'error' => 'Moi ca phai trong 07:00-17:00 va it nhat 4 gio.',
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
                'error' => 'Khong the sua ngay da duoc nhan don: ' . $lockedDays->implode(', '),
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
            'message' => 'Da cap nhat lich lam viec.',
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
        $user = $request->user();
        $taiKhoan = $user?->taiKhoan;
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi duoc dang ky lich.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'schedules' => ['required', 'array', 'min:1'],
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
                'error' => 'Chon it nhat 5 ngay trong tuan.',
            ], 422);
        }

        $normalized = [];
        $dates = [];

        foreach ($input as $schedule) {
            $date = Carbon::parse($schedule['date']);
            $start = Carbon::createFromFormat('H:i', $schedule['start_time']);
            $end = Carbon::createFromFormat('H:i', $schedule['end_time']);

            $startMinutes = $start->hour * 60 + $start->minute;
            $endMinutes = $end->hour * 60 + $end->minute;

            if ($end->lessThanOrEqualTo($start)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gio ket thuc phai lon hon gio bat dau.',
                ], 422);
            }

            if ($startMinutes < 7 * 60 || $endMinutes > 17 * 60 || ($endMinutes - $startMinutes) < 240) {
                return response()->json([
                    'success' => false,
                    'error' => 'Moi ca phai trong 07:00-17:00 va it nhat 4 gio.',
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
                'error' => 'Khong the sua ngay da duoc nhan don: ' . $lockedDays->implode(', '),
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
            'message' => 'Da luu lich lam viec.',
            'data' => [
                'staff_id' => $nhanVien->ID_NV,
                'schedules' => $results,
            ],
        ]);
    }
}
