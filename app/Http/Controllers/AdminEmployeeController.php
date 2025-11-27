<?php

namespace App\Http\Controllers;

use App\Models\NhanVien;
use Illuminate\Http\Request;

class AdminEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = NhanVien::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('Ten_NV', 'like', "%{$search}%")
                  ->orWhere('SDT', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $employees = $query->with('taiKhoan')->paginate(10);

        return view('admin.employees.index', compact('employees'));
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
}
