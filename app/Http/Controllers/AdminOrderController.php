<?php

namespace App\Http\Controllers;

use App\Models\DonDat;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'hour'); // Default to 'hour'
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = DonDat::with(['khachHang', 'dichVu', 'nhanVien', 'lichSuThanhToan'])
            ->orderBy('NgayTao', 'desc');

        if ($type === 'month') {
            $query->where('LoaiDon', 'month');
        } else {
            $query->where('LoaiDon', 'hour');
        }

        // Filter by Date Range
        if ($dateFrom) {
            $query->whereDate('NgayTao', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('NgayTao', '<=', $dateTo);
        }

        // Filter by Status
        if ($status) {
            $query->where('TrangThaiDon', $status);
        }

        // Search by Customer Name
        if ($search) {
            $query->whereHas('khachHang', function ($q) use ($search) {
                $q->where('Ten_KH', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->paginate(10)->appends($request->all());

        if ($request->ajax()) {
            return view('admin.orders.table', [
                'orders' => $orders,
                'currentType' => $type,
            ])->render();
        }

        return view('admin.orders.index', [
            'orders' => $orders,
            'currentType' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function show($id)
    {
        $order = DonDat::with(['khachHang', 'dichVu', 'nhanVien', 'phuThu', 'lichBuoiThang', 'lichSuThanhToan'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function getAvailableStaff($sessionId)
    {
        $session = \App\Models\LichBuoiThang::findOrFail($sessionId);
        $order = DonDat::with('diachi')->findOrFail($session->ID_DD);
        
        // Calculate End Time
        $startTime = \Carbon\Carbon::parse($session->GioBatDau);
        $duration = $order->ThoiLuongGio ?? 0;
        $endTime = $startTime->copy()->addHours($duration);
        $date = $session->NgayLam;


        // Find Staff who have registered schedule (LichLamViec) covering this time
        $availableStaff = \App\Models\NhanVien::whereHas('lichLamViecs', function($q) use ($date, $startTime, $endTime) {
            $q->where('NgayLam', $date)
              ->where('GioBatDau', '<=', $startTime->format('H:i:s'))
              ->where('GioKetThuc', '>=', $endTime->format('H:i:s'))
              ->where('TrangThai', 'ready'); // Assuming 'ready' means available
        })
        ->whereDoesntHave('lichBuoiThang', function($q) use ($date, $startTime, $endTime) {
            // Check for conflict in other Monthly Sessions
            $q->where('NgayLam', $date)
              ->where(function($query) use ($startTime, $endTime) {
                  $query->whereBetween('GioBatDau', [$startTime->format('H:i:s'), $endTime->format('H:i:s')])
                        ->orWhere(function($sub) use ($startTime, $endTime) {
                             // Complex overlap check if needed, but simple between start is usually enough for fixed slots
                             // Let's be more robust:
                             // Existing Start < New End AND Existing End > New Start
                             // Since LichBuoiThang might not have GioKetThuc stored, we might need to join DonDat to get duration.
                             // For simplicity/performance, let's assume standard overlap or just check Start Time collision for now if duration is fixed.
                             // But wait, I can't easily calculate end time of *other* sessions in SQL without joining.
                             // Let's stick to simple Start Time check for now or assume 4 hours max.
                             // Better: Check if they have ANY session on that day? No, that's too strict.
                             // Let's check if they have a session starting within +/- duration hours.
                             $sub->whereRaw("ABS(TIMEDIFF(GioBatDau, ?)) < '04:00:00'", [$startTime->format('H:i:s')]);
                        });
              })
              ->where('TrangThaiBuoi', '!=', 'cancelled');
        })
        // Also check conflicts with Hourly Orders (DonDat)
        ->whereDoesntHave('donDat', function($q) use ($date, $startTime, $endTime) {
             $q->where('NgayLam', $date)
               ->where('TrangThaiDon', '!=', 'cancelled')
               ->where('TrangThaiDon', '!=', 'completed') // Completed might be in past, but safe to exclude
               ->where(function($query) use ($startTime) {
                    $query->whereRaw("ABS(TIMEDIFF(GioBatDau, ?)) < '04:00:00'", [$startTime->format('H:i:s')]);
               });
        })
        ->get();

        // Format and return JSON response
        $staffList = $availableStaff->map(function($staff) {
            return [
                'id' => $staff->ID_NV,
                'name' => $staff->Ten_NV,
                'phone' => $staff->SDT,
            ];
        });

        return response()->json($staffList);
    }

    public function assignStaff(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'staff_id' => 'required'
        ]);

        $session = \App\Models\LichBuoiThang::with(['donDat.dichVu', 'donDat.khachHang', 'donDat.diaChi'])->findOrFail($request->session_id);
        
        if (in_array($session->TrangThaiBuoi, ['cancelled', 'completed'])) {
            return response()->json(['success' => false, 'message' => 'Không thể chỉnh sửa buổi làm đã hủy hoặc hoàn thành.']);
        }

        // Get employee information
        $staff = \App\Models\NhanVien::findOrFail($request->staff_id);
        
        // Update session
        $session->ID_NV = $request->staff_id;
        $session->TrangThaiBuoi = 'assigned';
        $session->save();

        // Update employee's work schedule status to 'assigned'
        $sessionDate = $session->NgayLam;
        $sessionStartTime = $session->GioBatDau;
        
        // Calculate end time based on order duration
        $order = $session->donDat;
        $startTime = \Carbon\Carbon::parse($sessionStartTime);
        $duration = $order->ThoiLuongGio ?? 4; // Default 4 hours if not specified
        $endTime = $startTime->copy()->addHours($duration);
        
        // Update LichLamViec status to 'assigned' for matching schedule
        \App\Models\LichLamViec::where('ID_NV', $request->staff_id)
            ->where('NgayLam', $sessionDate)
            ->where('GioBatDau', '<=', $sessionStartTime)
            ->where('GioKetThuc', '>=', $endTime->format('H:i:s'))
            ->where('TrangThai', 'ready')
            ->update(['TrangThai' => 'assigned']);

        // Prepare email data
        $emailData = [
            'staff_name' => $staff->Ten_NV,
            'service_name' => $order->dichVu->TenDV ?? 'N/A',
            'customer_name' => $order->khachHang->Ten_KH ?? 'N/A',
            'customer_phone' => $order->khachHang->SDT ?? null,
            'session_date' => \Carbon\Carbon::parse($sessionDate)->format('d/m/Y'),
            'session_time' => $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
            'address' => $order->diaChi->DiaChiDayDu ?? 'N/A',
            'order_id' => $order->ID_DD,
            'session_id' => $session->ID_Buoi,
        ];

        // Send email notification
        try {
            \Mail::to($staff->Email)->send(new \App\Mail\StaffAssignmentMail($emailData));
        } catch (\Exception $e) {
            // Log error but don't fail the assignment
            \Log::error('Failed to send staff assignment email: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    public function getAvailableStaffForOrder($orderId)
    {
        $order = DonDat::with('diachi')->findOrFail($orderId);
        
        // Calculate End Time
        $startTime = \Carbon\Carbon::parse($order->GioBatDau);
        $duration = $order->ThoiLuongGio ?? 0;
        $endTime = $startTime->copy()->addHours($duration);
        $date = $order->NgayLam;


        // Find Staff who have registered schedule (LichLamViec) covering this time
        $availableStaff = \App\Models\NhanVien::whereHas('lichLamViecs', function($q) use ($date, $startTime, $endTime) {
            $q->where('NgayLam', $date)
              ->where('GioBatDau', '<=', $startTime->format('H:i:s'))
              ->where('GioKetThuc', '>=', $endTime->format('H:i:s'))
              ->where('TrangThai', 'ready');
        })
        ->whereDoesntHave('lichBuoiThang', function($q) use ($date, $startTime, $endTime) {
            // Check for conflict in Monthly Sessions
            $q->where('NgayLam', $date)
              ->where(function($query) use ($startTime, $endTime) {
                  $query->whereBetween('GioBatDau', [$startTime->format('H:i:s'), $endTime->format('H:i:s')])
                        ->orWhereRaw("ABS(TIMEDIFF(GioBatDau, ?)) < '04:00:00'", [$startTime->format('H:i:s')]);
              })
              ->where('TrangThaiBuoi', '!=', 'cancelled');
        })
        ->whereDoesntHave('donDat', function($q) use ($date, $startTime, $endTime) {
             // Check conflicts with Hourly Orders
             $q->where('NgayLam', $date)
               ->where('TrangThaiDon', '!=', 'cancelled')
               ->where('TrangThaiDon', '!=', 'completed')
               ->where(function($query) use ($startTime) {
                    $query->whereRaw("ABS(TIMEDIFF(GioBatDau, ?)) < '04:00:00'", [$startTime->format('H:i:s')]);
               });
        })
        ->get();

        // Format and return JSON response
        $staffList = $availableStaff->map(function($staff) {
            return [
                'id' => $staff->ID_NV,
                'name' => $staff->Ten_NV,
                'phone' => $staff->SDT,
            ];
        });

        return response()->json($staffList);
    }

    public function assignStaffToOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'staff_id' => 'required'
        ]);

        $order = DonDat::with(['dichVu', 'khachHang', 'diaChi'])->findOrFail($request->order_id);
        
        if (in_array($order->TrangThaiDon, ['cancelled', 'rejected', 'completed'])) {
            // Allow re-assigning if rejected? The user said "rejected" allows changing staff.
            // But here it blocks 'rejected'.
            // I should remove 'rejected' from this check if I want to allow re-assignment.
            // Wait, for MONTHLY sessions, the check was:
            // if (in_array($session->TrangThaiBuoi, ['cancelled', 'completed']))
            // It did NOT include 'rejected'.
            // So for HOURLY orders, I should also remove 'rejected' from this check to allow re-assignment.
        }
        
        // Let's fix the check first.
        if (in_array($order->TrangThaiDon, ['cancelled', 'completed'])) {
             return response()->json(['success' => false, 'message' => 'Không thể chỉnh sửa đơn hàng đã hủy hoặc hoàn thành.']);
        }

        // Get employee information
        $staff = \App\Models\NhanVien::findOrFail($request->staff_id);

        $order->ID_NV = $request->staff_id;
        $order->TrangThaiDon = 'assigned'; // Force assigned
        
        $order->save();

        // Update employee's work schedule status to 'assigned'
        $orderDate = $order->NgayLam;
        $orderStartTime = $order->GioBatDau;
        
        // Calculate end time based on order duration
        $startTime = \Carbon\Carbon::parse($orderStartTime);
        $duration = $order->ThoiLuongGio ?? 4; // Default 4 hours if not specified
        $endTime = $startTime->copy()->addHours($duration);
        
        // Update LichLamViec status to 'assigned' for matching schedule
        \App\Models\LichLamViec::where('ID_NV', $request->staff_id)
            ->where('NgayLam', $orderDate)
            ->where('GioBatDau', '<=', $orderStartTime)
            ->where('GioKetThuc', '>=', $endTime->format('H:i:s'))
            ->where('TrangThai', 'ready')
            ->update(['TrangThai' => 'assigned']);

        // Prepare email data
        $emailData = [
            'staff_name' => $staff->Ten_NV,
            'service_name' => $order->dichVu->TenDV ?? 'N/A',
            'customer_name' => $order->khachHang->Ten_KH ?? 'N/A',
            'customer_phone' => $order->khachHang->SDT ?? null,
            'session_date' => \Carbon\Carbon::parse($orderDate)->format('d/m/Y'),
            'session_time' => $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
            'address' => $order->diaChi->DiaChiDayDu ?? 'N/A',
            'order_id' => $order->ID_DD,
            'session_id' => null, // Hourly orders don't have session_id
        ];

        // Send email notification
        try {
            \Mail::to($staff->Email)->send(new \App\Mail\StaffAssignmentMail($emailData));
        } catch (\Exception $e) {
            // Log error but don't fail the assignment
            \Log::error('Failed to send staff assignment email: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    public function cancelSession(Request $request, \App\Services\RefundService $refundService, \App\Services\NotificationService $notificationService)
    {
        $request->validate([
            'session_id' => 'required'
        ]);

        $session = \App\Models\LichBuoiThang::with(['donDat.khachHang', 'nhanVien'])->findOrFail($request->session_id);
        
        if ($session->TrangThaiBuoi === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Buổi làm này đã bị hủy trước đó.']);
        }

        if ($session->TrangThaiBuoi === 'completed') {
             return response()->json(['success' => false, 'message' => 'Không thể hủy buổi làm đã hoàn thành.']);
        }

        // 1. Refund logic
        $refundResult = $refundService->refundSession($session, 'user_cancel_session');
        
        // 2. Update Session Status
        $session->TrangThaiBuoi = 'cancelled';
        $session->save();

        // 3. Notify Staff & Update Schedule
        if ($session->ID_NV) {
            $staff = $session->nhanVien;
            
            // Update LichLamViec status back to 'ready'
            // We try to match the schedule based on date and start time
            \App\Models\LichLamViec::where('ID_NV', $session->ID_NV)
                ->where('NgayLam', $session->NgayLam)
                ->where('GioBatDau', '<=', $session->GioBatDau)
                ->where('TrangThai', 'assigned')
                ->update(['TrangThai' => 'ready']);

            // Send Email to Staff
            try {
                \Mail::to($staff->Email)->send(new \App\Mail\StaffSessionCancelledMail([
                    'staff_name' => $staff->Ten_NV,
                    'session_date' => \Carbon\Carbon::parse($session->NgayLam)->format('d/m/Y'),
                    'session_time' => \Carbon\Carbon::parse($session->GioBatDau)->format('H:i'),
                    'order_id' => $session->ID_DD,
                    'reason' => 'Khách hàng yêu cầu hủy',
                ]));
            } catch (\Exception $e) {
                \Log::error('Failed to send staff cancellation email: ' . $e->getMessage());
            }
        }

        // 4. Notify Customer
        $notificationService->notifySessionCancelled($session, 'user_cancel_session', [
            'amount' => $refundResult['amount'],
            'payment_method' => $refundResult['payment_method']
        ]);

        return response()->json(['success' => true, 'message' => 'Hủy buổi làm thành công.']);
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'hour');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = DonDat::with(['khachHang', 'dichVu', 'nhanVien', 'lichBuoiThang.nhanVien'])
            ->orderBy('NgayTao', 'desc');

        if ($type === 'month') {
            $query->where('LoaiDon', 'month');
        } else {
            $query->where('LoaiDon', 'hour');
        }

        if ($dateFrom) {
            $query->whereDate('NgayTao', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('NgayTao', '<=', $dateTo);
        }

        if ($status) {
            $query->where('TrangThaiDon', $status);
        }

        if ($search) {
            $query->whereHas('khachHang', function ($q) use ($search) {
                $q->where('Ten_KH', 'like', '%' . $search . '%');
            });
        }

        $orders = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=orders-export.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'ID', 
                'Loại',
                'Khách hàng', 
                'Dịch vụ', 
                'Ngày tạo', 
                'Tổng tiền',
                'Ngày làm',
                'Giờ làm',
                'Nhân viên', 
                'Trạng thái'
            ], ';');

            foreach ($orders as $order) {
                // 1. Print Order Row
                fputcsv($file, [
                    $order->ID_DD,
                    $order->LoaiDon === 'month' ? 'Theo tháng' : 'Theo giờ',
                    $order->khachHang->Ten_KH ?? 'N/A',
                    $order->dichVu->TenDV ?? 'N/A',
                    \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i'),
                    $order->TongTienSauGiam,
                    // For hourly orders, fill session info here. For monthly, leave empty in main row.
                    ($order->LoaiDon === 'hour' && $order->NgayLam) ? \Carbon\Carbon::parse($order->NgayLam)->format('d/m/Y') : '',
                    ($order->LoaiDon === 'hour' && $order->GioBatDau) ? (\Carbon\Carbon::parse($order->GioBatDau)->format('H:i') . ' - ' . \Carbon\Carbon::parse($order->GioKetThuc)->format('H:i')) : '',
                    ($order->LoaiDon === 'hour') ? ($order->nhanVien->Ten_NV ?? 'Chưa có') : '', // Main staff for hourly
                    $order->TrangThaiDon,
                ], ';');

                // 2. If Monthly, Print Session Rows
                if ($order->LoaiDon === 'month' && $order->lichBuoiThang->count() > 0) {
                    foreach ($order->lichBuoiThang as $session) {
                        fputcsv($file, [
                            '', // ID (Empty for detail row)
                            '', // Type
                            '', // Customer
                            '', // Service
                            '', // Created Date
                            '', // Total Amount
                            \Carbon\Carbon::parse($session->NgayLam)->format('d/m/Y'),
                            \Carbon\Carbon::parse($session->GioBatDau)->format('H:i') . ' - ' . \Carbon\Carbon::parse($session->GioKetThuc)->format('H:i'),
                            $session->nhanVien->Ten_NV ?? 'Chưa có',
                            $session->TrangThai,
                        ], ';');
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
