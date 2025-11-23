<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiaChi;
use App\Models\DichVu;
use App\Models\DonDat;
use App\Models\LichLamViec;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiStaffBookingController extends Controller
{
    private function requireStaff(Request $request)
    {
        $user = $request->user();
        $taiKhoan = $user?->taiKhoan;
        $nhanVien = $taiKhoan?->nhanVien;

        if (!$taiKhoan || $taiKhoan->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return null;
        }

        return $nhanVien;
    }

    /**
     * List bookings assigned to the staff (default: assigned + confirmed)
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
        if ($statusFilter === 'rejected') {
            $statuses = ['rejected'];
        } elseif ($statusFilter === 'all') {
            $statuses = ['assigned', 'confirmed', 'rejected', 'done', 'cancelled', 'finding_staff'];
        } else {
            $statuses = ['assigned', 'confirmed'];
        }

        $bookings = DonDat::where('ID_NV', $nhanVien->ID_NV)
            ->whereIn('TrangThaiDon', $statuses)
            ->orderByDesc('NgayLam')
            ->get()
            ->map(function (DonDat $booking) {
                return [
                    'id' => $booking->ID_DD,
                    'service_id' => $booking->ID_DV,
                    'work_date' => $booking->NgayLam,
                    'start_time' => $booking->GioBatDau ? substr($booking->GioBatDau, 0, 5) : null,
                    'duration_hours' => (float) $booking->ThoiLuongGio,
                    'status' => $booking->TrangThaiDon,
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
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'error' => 'Khong tim thay don dat.',
            ], 404);
        }

        $service = DichVu::find($booking->ID_DV);
        $address = DiaChi::find($booking->ID_DC);

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
                'created_at' => $booking->NgayTao,
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

        $booking->TrangThaiDon = 'confirmed';
        $booking->save();

        // Mark the matching schedule as assigned so it is counted
        $this->touchScheduleStatus($nhanVien->ID_NV, $booking, 'assigned');

        return response()->json([
            'success' => true,
            'message' => 'Da nhan don.',
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

        $booking->TrangThaiDon = 'rejected';
        $booking->save();

        // Ensure schedule stays ready (not counted as assigned)
        $this->touchScheduleStatus($nhanVien->ID_NV, $booking, 'ready');

        return response()->json([
            'success' => true,
            'message' => 'Da tu choi don.',
        ]);
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
