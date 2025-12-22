<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\DonDat;
use App\Models\NhanVien;
use App\Models\User;
use App\Models\KhachHang;
use App\Models\DanhGiaNhanVien;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Date Range Filter
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // 2. KPIs
        
        // Total Orders (Range)
        $totalOrdersRange = DonDat::whereBetween('NgayTao', [$startDate, $endDate])->count();
        
        // Keep these for small text if needed
        $totalOrdersDay = DonDat::whereDate('NgayTao', Carbon::today())->count();
        $totalOrdersWeek = DonDat::whereBetween('NgayTao', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();

        // Count all statuses in the range
        $orderStatusCounts = DonDat::select('TrangThaiDon', DB::raw('count(*) as total'))
            ->whereBetween('NgayTao', [$startDate, $endDate])
            ->groupBy('TrangThaiDon')
            ->pluck('total', 'TrangThaiDon')
            ->toArray();

        $allStatusConfig = [
            'finding_staff' => [
                'label' => 'Đơn đang tìm NV',
                'icon' => 'person_search',
                'class' => 'warning',
            ],
            'confirmed' => [
                'label' => 'Đơn đã xác nhận',
                'icon' => 'verified', // or check_circle_outline
                'class' => 'primary',
            ],
            'assigned' => [
                'label' => 'Đơn đã có NV',
                'icon' => 'badge', // or engineering
                'class' => 'info',
            ],
             'rejected' => [
                'label' => 'Đơn NV từ chối',
                'icon' => 'person_off',
                'class' => 'danger',
            ],
            'completed' => [
                'label' => 'Đơn đã hoàn thành',
                'icon' => 'check_circle',
                'class' => 'success',
            ],
            'cancelled' => [
                'label' => 'Đơn đã huỷ',
                'icon' => 'cancel',
                'class' => 'danger',
            ],
        ];

        // Specific vars for top cards (backward compatibility or specific highlight)
        $pendingOrders = $orderStatusCounts['confirmed'] ?? 0; // Or finding_staff? Let's use confirmed/assigned/finding_staff sum if "Pending" implies action needed. 
        $pendingOrders = ($orderStatusCounts['assigned'] ?? 0); 
        $inProgressOrders = ($orderStatusCounts['cancelled'] ?? 0); // Previous code mapped this variable to 'cancelled' status for the "Đơn Huỷ" card.
        $completedOrders = ($orderStatusCounts['completed'] ?? 0);

        $workingStaff = NhanVien::count();
        $totalCustomers = KhachHang::count();

        // Revenue calculation including completed monthly sessions
        // 1. Revenue from completed hourly orders
        $hourlyRevenueRange = DonDat::whereBetween('NgayTao', [$startDate, $endDate])
            ->where('LoaiDon', 'hour')
            ->where('TrangThaiDon', 'completed')
            ->sum('TongTienSauGiam');

        // 2. Revenue from completed monthly sessions (proportional)
        $monthlySessionsRange = \App\Models\LichBuoiThang::with('donDat')
            ->whereBetween('NgayLam', [$startDate, $endDate])
            ->where('TrangThaiBuoi', 'completed')
            ->get();
        
        $monthlyRevenueRange = 0;
        foreach ($monthlySessionsRange as $session) {
            if ($session->donDat) {
                $totalSessions = \App\Models\LichBuoiThang::where('ID_DD', $session->ID_DD)->count();
                if ($totalSessions > 0) {
                    $monthlyRevenueRange += $session->donDat->TongTienSauGiam / $totalSessions;
                }
            }
        }
        
        $revenueRange = $hourlyRevenueRange + $monthlyRevenueRange;

        // Daily revenue (similar logic)
        $hourlyRevenueDay = DonDat::whereDate('NgayTao', Carbon::today())
            ->where('LoaiDon', 'hour')
            ->where('TrangThaiDon', 'completed')
            ->sum('TongTienSauGiam');

        $monthlySessionsDay = \App\Models\LichBuoiThang::with('donDat')
            ->whereDate('NgayLam', Carbon::today())
            ->where('TrangThaiBuoi', 'completed')
            ->get();
        
        $monthlyRevenueDay = 0;
        foreach ($monthlySessionsDay as $session) {
            if ($session->donDat) {
                $totalSessions = \App\Models\LichBuoiThang::where('ID_DD', $session->ID_DD)->count();
                if ($totalSessions > 0) {
                    $monthlyRevenueDay += $session->donDat->TongTienSauGiam / $totalSessions;
                }
            }
        }
        
        $revenueDay = $hourlyRevenueDay + $monthlyRevenueDay;
            
        // Weekly revenue (similar logic)
        $hourlyRevenueWeek = DonDat::whereBetween('NgayTao', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->where('LoaiDon', 'hour')
            ->where('TrangThaiDon', 'completed')
            ->sum('TongTienSauGiam');

        $monthlySessionsWeek = \App\Models\LichBuoiThang::with('donDat')
            ->whereBetween('NgayLam', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->where('TrangThaiBuoi', 'completed')
            ->get();
        
        $monthlyRevenueWeek = 0;
        foreach ($monthlySessionsWeek as $session) {
            if ($session->donDat) {
                $totalSessions = \App\Models\LichBuoiThang::where('ID_DD', $session->ID_DD)->count();
                if ($totalSessions > 0) {
                    $monthlyRevenueWeek += $session->donDat->TongTienSauGiam / $totalSessions;
                }
            }
        }
        
        $revenueWeek = $hourlyRevenueWeek + $monthlyRevenueWeek;


        // 3. Charts Data

        $ordersByDayData = DonDat::select(DB::raw('DATE(NgayTao) as date'), DB::raw('count(*) as total'))
            ->whereBetween('NgayTao', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
        
        $ordersByDayLabels = $ordersByDayData->pluck('date');
        $ordersByDayValues = $ordersByDayData->pluck('total');

        // Pie Chart: Service Distribution (in selected range)
        $serviceDistributionData = DonDat::join('DichVu', 'DonDat.ID_DV', '=', 'DichVu.ID_DV')
            ->select('DichVu.TenDV', DB::raw('count(*) as total'))
            ->whereBetween('DonDat.NgayTao', [$startDate, $endDate])
            ->groupBy('DichVu.TenDV')
            ->get();

        $serviceDistributionLabels = $serviceDistributionData->pluck('TenDV');
        $serviceDistributionValues = $serviceDistributionData->pluck('total');

        // Pie Chart: Order Type Distribution (in selected range)
        $orderTypeData = DonDat::select('LoaiDon', DB::raw('count(*) as total'))
            ->whereBetween('NgayTao', [$startDate, $endDate])
            ->groupBy('LoaiDon')
            ->get();

        $orderTypeLabels = $orderTypeData->pluck('LoaiDon')->map(function($type) {
            return $type === 'hour' ? 'Theo giờ' : ($type === 'month' ? 'Theo tháng' : $type);
        });
        $orderTypeValues = $orderTypeData->pluck('total');


        // Recent Orders (Keep existing logic but maybe limit)
        $recentOrders = DonDat::with(['khachHang', 'dichVu']) // Assuming relationships exist
            ->orderBy('NgayTao', 'desc')
            ->take(5)
            ->get();
            
        $deliverStatusLabels = [
            'finding_staff' => 'Tìm nhân viên',
            'confirmed' => 'Đã xác nhận',
            'assigned' => 'Đã có nhân viên',
            'rejected' => 'Nhân viên từ chối',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.dashboard', compact(
            'startDate',
            'endDate',
            'totalOrdersDay',
            'totalOrdersRange',
            'totalOrdersDay',
            'totalOrdersWeek',
            'pendingOrders',
            'inProgressOrders',
            'completedOrders',
            'workingStaff',
            'totalCustomers',
            'revenueRange',
            'revenueDay',
            'revenueWeek',
            'ordersByDayLabels',
            'ordersByDayValues',
            'serviceDistributionLabels',
            'serviceDistributionValues',
            'orderTypeLabels',
            'orderTypeValues',
            'recentOrders',
            'deliverStatusLabels',
            'orderStatusCounts',
            'allStatusConfig'
        ));
    }

    public function exportRevenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get completed hourly orders
        $hourlyOrders = DonDat::with(['dichVu', 'khachHang', 'nhanVien'])
            ->where('LoaiDon', 'hour')
            ->where('TrangThaiDon', 'completed')
            ->whereBetween('NgayLam', [$startDate, $endDate])
            ->get();

        // Get completed monthly sessions
        $monthlySessions = \App\Models\LichBuoiThang::with(['donDat.dichVu', 'donDat.khachHang', 'nhanVien'])
            ->where('TrangThaiBuoi', 'completed')
            ->whereBetween('NgayLam', [$startDate, $endDate])
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=revenue-report-{$startDate}-{$endDate}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($hourlyOrders, $monthlySessions, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel to recognize UTF-8
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'Loại', 
                'Mã đơn/buổi', 
                'Dịch vụ', 
                'Khách hàng', 
                'Nhân viên', 
                'Ngày làm', 
                'Doanh thu'
            ], ';');

            // Export hourly orders
            foreach ($hourlyOrders as $order) {
                fputcsv($file, [
                    'Đơn theo giờ',
                    $order->ID_DD,
                    $order->dichVu->TenDV ?? 'N/A',
                    $order->khachHang->Ten_KH ?? 'N/A',
                    $order->nhanVien->Ten_NV ?? 'N/A',
                    $order->NgayLam ? Carbon::parse($order->NgayLam)->format('d/m/Y') : 'N/A',
                    number_format(round($order->TongTienSauGiam), 0, ',', '.')
                ], ';');
            }

            // Export monthly sessions
            foreach ($monthlySessions as $session) {
                if ($session->donDat) {
                    $totalSessions = \App\Models\LichBuoiThang::where('ID_DD', $session->ID_DD)->count();
                    $sessionRevenue = $totalSessions > 0 
                        ? $session->donDat->TongTienSauGiam / $totalSessions 
                        : 0;

                    fputcsv($file, [
                        'Buổi theo tháng',
                        $session->ID_Buoi,
                        $session->donDat->dichVu->TenDV ?? 'N/A',
                        $session->donDat->khachHang->Ten_KH ?? 'N/A',
                        $session->nhanVien->Ten_NV ?? 'N/A',
                        Carbon::parse($session->NgayLam)->format('d/m/Y'),
                        number_format(round($sessionRevenue), 0, ',', '.')
                    ], ';');
                }
            }

            // Summary row
            $totalHourly = $hourlyOrders->sum('TongTienSauGiam');
            $totalMonthly = 0;
            foreach ($monthlySessions as $session) {
                if ($session->donDat) {
                    $totalSessions = \App\Models\LichBuoiThang::where('ID_DD', $session->ID_DD)->count();
                    if ($totalSessions > 0) {
                        $totalMonthly += $session->donDat->TongTienSauGiam / $totalSessions;
                    }
                }
            }
            
            fputcsv($file, [], ';');
            fputcsv($file, ['TỔNG CỘNG', '', '', '', '', '', number_format(round($totalHourly + $totalMonthly), 0, ',', '.')], ';');
            fputcsv($file, ['Đơn theo giờ', '', '', '', '', '', number_format(round($totalHourly), 0, ',', '.')], ';');
            fputcsv($file, ['Buổi theo tháng', '', '', '', '', '', number_format(round($totalMonthly), 0, ',', '.')], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
