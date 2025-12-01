<?php

namespace App\Http\Controllers;

use App\Models\KhachHang;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = KhachHang::query();

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('Ten_KH', 'like', "%{$search}%")
                  ->orWhere('SDT', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $customers = $query->with(['taiKhoan', 'donDats'])->paginate(10);

        return view('admin.customers.index', compact('customers'));
    }

    public function export()
    {
        $customers = KhachHang::with(['taiKhoan', 'donDats'])->get();
        $csvFileName = 'customers_export_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($customers) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, ['Họ và Tên', 'Số điện thoại', 'Email', 'Tổng chi tiêu', 'Trạng thái'], ";");

            foreach ($customers as $customer) {
                $status = optional($customer->taiKhoan)->TrangThaiTK;
                $statusText = match ($status) {
                    'active' => 'Hoạt động',
                    'banned' => 'Đã khóa',
                    default => 'Chưa kích hoạt',
                };

                $totalSpend = $customer->donDats->sum('TongTienSauGiam');

                fputcsv($file, [
                    $customer->Ten_KH,
                    $customer->SDT,
                    $customer->Email,
                    $totalSpend, // Send raw number for Excel calculation
                    $statusText
                ], ";");
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function updateStatus(KhachHang $customer)
    {
        $taiKhoan = $customer->taiKhoan;
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
