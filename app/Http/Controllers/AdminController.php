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

        $revenueRange = DonDat::whereBetween('NgayTao', [$startDate, $endDate])
            ->whereIn('TrangThaiDon', ['completed'])
            ->sum('TongTienSauGiam');

        $revenueDay = DonDat::whereDate('NgayTao', Carbon::today())
            ->whereIn('TrangThaiDon', ['completed'])
            ->sum('TongTienSauGiam');
            
        $revenueWeek = DonDat::whereBetween('NgayTao', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->whereIn('TrangThaiDon', ['completed'])
            ->sum('TongTienSauGiam');


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

        $data = DonDat::join('DichVu', 'DonDat.ID_DV', '=', 'DichVu.ID_DV')
            ->select(
                'DichVu.TenDV',
                DB::raw('SUM(DonDat.TongTienSauGiam) as DoanhThu'),
                DB::raw('COUNT(DISTINCT DonDat.ID_KH) as SoKhachHang')
            )
            ->whereBetween('DonDat.NgayTao', [$startDate, $endDate])
            ->where('DonDat.TrangThaiDon', 'completed')
            ->groupBy('DichVu.ID_DV', 'DichVu.TenDV')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=revenue-report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel to recognize UTF-8
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, ['Tên dịch vụ', 'Doanh thu', 'Số khách hàng đặt'], ';');

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->TenDV, 
                    $row->DoanhThu, 
                    $row->SoKhachHang
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
