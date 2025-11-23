<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));

        // Dummy KPI Cards
        $kpiCards = [
            [
                'theme' => 'primary',
                'icon' => 'shopping_cart',
                'label' => 'Total Sales',
                'value' => '25,024,000 đ',
                'range' => 'Last 24 Hours',
                'note' => '+15% from yesterday',
                'arc_delay' => 0,
                'arc_offset' => 0.75,
            ],
            [
                'theme' => 'danger',
                'icon' => 'local_mall',
                'label' => 'Total Orders',
                'value' => '15',
                'range' => 'Last 24 Hours',
                'note' => '-5% from yesterday',
                'arc_delay' => 100,
                'arc_offset' => 0.5,
            ],
            [
                'theme' => 'success',
                'icon' => 'person',
                'label' => 'New Customers',
                'value' => '5',
                'range' => 'Last 24 Hours',
                'note' => '+20% from yesterday',
                'arc_delay' => 200,
                'arc_offset' => 0.3,
            ],
        ];

        $bestSellerRangeNote = 'Last 7 Days';
        $bestSellingProducts = collect([
            (object)['name' => 'Service A', 'total_quantity' => 10, 'total_sales' => 5000000],
            (object)['name' => 'Service B', 'total_quantity' => 8, 'total_sales' => 3000000],
            (object)['name' => 'Service C', 'total_quantity' => 5, 'total_sales' => 1500000],
        ]);
        $bestSellerTotalQty = $bestSellingProducts->sum('total_quantity');

        $rangeLabel = 'Today';
        $recentOrders = collect([
            (object)[
                'order_id' => 1,
                'customer_name' => 'Nguyen Van A',
                'payment_method' => 'COD',
                'net_total' => 500000,
                'status' => 'paid',
                'deliver_status' => 'completed',
                'order_date' => Carbon::now(),
            ],
            (object)[
                'order_id' => 2,
                'customer_name' => 'Tran Thi B',
                'payment_method' => 'VNPAY',
                'net_total' => 1200000,
                'status' => 'unpaid',
                'deliver_status' => 'waiting_confirmation',
                'order_date' => Carbon::now()->subHours(2),
            ],
        ]);

        $deliverStatusLabels = [
            'waiting_confirmation' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'preparing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        $statusBreakdown = collect([
            (object)['deliver_status' => 'waiting_confirmation', 'total' => 2],
            (object)['deliver_status' => 'completed', 'total' => 5],
        ]);

        $dashboardStats = [
            'orders' => 7,
        ];

        return view('admin.dashboard', compact(
            'selectedDate',
            'kpiCards',
            'bestSellerRangeNote',
            'bestSellingProducts',
            'bestSellerTotalQty',
            'rangeLabel',
            'recentOrders',
            'deliverStatusLabels',
            'statusBreakdown',
            'dashboardStats'
        ));
    }
}
