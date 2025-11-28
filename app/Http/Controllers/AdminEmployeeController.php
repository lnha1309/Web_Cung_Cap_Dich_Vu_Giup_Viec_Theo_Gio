<?php

namespace App\Http\Controllers;

use App\Models\NhanVien;
use Illuminate\Http\Request;

class AdminEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'));

        $query = NhanVien::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('Ten_NV', 'like', "%{$search}%")
                  ->orWhere('SDT', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $employees = $query->with(['taiKhoan', 'donDat' => function($q) use ($startDate, $endDate) {
            $q->whereIn('TrangThaiDon', ['completed', 'done'])
              ->whereBetween('NgayTao', [$startDate, $endDate]);
        }])->paginate(10);

        return view('admin.employees.index', compact('employees', 'startDate', 'endDate'));
    }
    public function updateStatus(NhanVien $employee)
    {
        $taiKhoan = $employee->taiKhoan;
        
        if ($taiKhoan) {
            $currentStatus = $taiKhoan->TrangThaiTK;
            
            if ($currentStatus === 'active') {
                $taiKhoan->TrangThaiTK = 'banned';
                $message = 'Đã khóa tài khoản thành công.';
            } else {
                // inactive or banned -> active
                $taiKhoan->TrangThaiTK = 'active';
                $message = 'Đã kích hoạt tài khoản thành công.';
            }
            
            $taiKhoan->save();
            return back()->with('success', $message);
        }
        
        return back()->with('error', 'Không tìm thấy tài khoản liên kết.');
    }

    public function exportRevenue(Request $request)
    {
        $startDate = $request->input('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'));

        $query = NhanVien::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('Ten_NV', 'like', "%{$search}%")
                  ->orWhere('SDT', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $employees = $query->with(['donDat' => function($q) use ($startDate, $endDate) {
            $q->whereIn('TrangThaiDon', ['completed', 'done'])
              ->whereBetween('NgayTao', [$startDate, $endDate]);
        }])->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=employee-revenue-report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($employees, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'ID', 
                'Họ và Tên', 
                'Số điện thoại', 
                'Email', 
                'Khu vực', 
                'Số dư', 
                "Doanh thu ($startDate - $endDate)"
            ], ';');

            foreach ($employees as $employee) {
                $revenue = $employee->donDat->sum('TongTienSauGiam');
                
                fputcsv($file, [
                    $employee->ID_NV,
                    $employee->Ten_NV,
                    $employee->SDT,
                    $employee->Email,
                    $employee->KhuVucLamViec,
                    $employee->SoDu,
                    $revenue
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
