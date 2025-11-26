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

        $query = DonDat::with(['khachHang', 'dichVu', 'nhanVien'])
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
        $order = DonDat::with(['khachHang', 'dichVu', 'nhanVien', 'phuThu', 'lichBuoiThang'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }
}
