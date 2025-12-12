<?php

namespace App\Http\Controllers;

use App\Models\NhanVien;
use App\Models\DanhGiaNhanVien;
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

        $employees = $query->with(['taiKhoan', 'danhGias', 'donDat' => function($q) use ($startDate, $endDate) {
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

    public function getSalaryData(Request $request)
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
        
        // Calculate salary data
        $salaryData = [];
        $totalEligible = 0;
        $totalSalary = 0;
        
        foreach ($employees as $employee) {
            $balance = $employee->SoDu ?? 0;
            $salary = $balance - 400000;
            $isEligible = $balance > 400000;
            
            if ($isEligible) {
                $totalEligible++;
                $totalSalary += $salary;
            }
            
            $salaryData[] = [
                'employee' => $employee,
                'salary' => $salary,
                'isEligible' => $isEligible
            ];
        }

        return view('admin.employees.salary', compact('employees', 'salaryData', 'totalEligible', 'totalSalary'));
    }

    public function exportSalary(Request $request)
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

        $employees = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=employee-salary-report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'ID', 
                'Họ và Tên', 
                'Số điện thoại', 
                'Email', 
                'Số dư', 
                'Lương nhận',
                'Trạng thái'
            ], ';');

            foreach ($employees as $employee) {
                $balance = $employee->SoDu ?? 0;
                $salary = $balance - 400000;
                $isEligible = $balance > 400000;
                
                fputcsv($file, [
                    $employee->ID_NV,
                    $employee->Ten_NV,
                    $employee->SDT,
                    $employee->Email,
                    $balance,
                    $isEligible ? $salary : 0,
                    $isEligible ? 'Đủ điều kiện' : 'Không đủ điều kiện'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function processSalaryPayment(Request $request)
    {
        try {
            \DB::beginTransaction();
            
            // Get all eligible employees
            $employees = NhanVien::where('SoDu', '>', 400000)->get();
            
            $processedCount = 0;
            $totalAmount = 0;
            
            foreach ($employees as $employee) {
                $salary = $employee->SoDu - 400000;
                
                // Update employee balance
                $oldBalance = $employee->SoDu;
                $employee->SoDu = 400000;
                $employee->save();
                
                // Create transaction history
                $transactionId = 'LSV' . now()->format('YmdHis') . str_pad($employee->ID_NV, 4, '0', STR_PAD_LEFT);
                
                \App\Models\LichSuViNhanVien::create([
                    'ID_LSV' => $transactionId,
                    'ID_NV' => $employee->ID_NV,
                    'LoaiGiaoDich' => 'salary_payout',
                    'Huong' => 'out',
                    'SoTien' => $salary,
                    'SoDuSau' => 400000,
                    'MoTa' => 'Thanh toán lương nhân viên',
                    'TrangThai' => 'completed',
                    'Nguon' => 'admin',
                ]);
                
                // Send email notification to employee
                if ($employee->Email) {
                    try {
                        \Mail::to($employee->Email)->send(new \App\Mail\SalaryPaymentMail([
                            'employee_name' => $employee->Ten_NV,
                            'employee_id' => $employee->ID_NV,
                            'salary_amount' => $salary,
                            'balance_before' => $oldBalance,
                            'balance_after' => 400000,
                            'transaction_id' => $transactionId,
                            'payment_date' => now()->format('d/m/Y H:i'),
                        ]));
                    } catch (\Exception $mailException) {
                        // Log mail error but don't stop the process
                        \Log::warning("Failed to send salary payment email to {$employee->Email}: " . $mailException->getMessage());
                    }
                }
                
                $processedCount++;
                $totalAmount += $salary;
            }
            
            \DB::commit();
            
            return back()->with('success', "Đã thanh toán lương thành công cho {$processedCount} nhân viên. Tổng số tiền: " . number_format($totalAmount) . "đ");
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi thanh toán lương: ' . $e->getMessage());
        }
    }

    public function getReviews(NhanVien $employee)
    {
        $reviews = DanhGiaNhanVien::where('ID_NV', $employee->ID_NV)
            ->orderBy('ThoiGian', 'desc')
            ->get()
            ->map(function($review) {
                $khachHang = \App\Models\KhachHang::find($review->ID_KH);
                return [
                    'id' => $review->ID_DG,
                    'diem' => $review->Diem,
                    'nhanXet' => $review->NhanXet,
                    'thoiGian' => $review->ThoiGian,
                    'khachHang' => $khachHang ? $khachHang->Ten_KH : 'Ẩn danh',
                ];
            });

        return response()->json([
            'employee' => [
                'id' => $employee->ID_NV,
                'name' => $employee->Ten_NV,
            ],
            'avgRating' => $reviews->avg('diem') ?? 0,
            'totalReviews' => $reviews->count(),
            'reviews' => $reviews,
        ]);
    }

    public function deleteReview($reviewId)
    {
        try {
            $review = DanhGiaNhanVien::where('ID_DG', $reviewId)->first();
            
            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy đánh giá'], 404);
            }
            
            $review->delete();
            
            return response()->json(['success' => true, 'message' => 'Đã xóa đánh giá thành công']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
}

