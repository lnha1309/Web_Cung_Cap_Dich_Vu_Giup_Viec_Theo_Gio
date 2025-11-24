@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
@push('styles')
<style>
    /* Remove duplicate root variables as they are in style.css */
    
    main {
        margin-top: 1.4rem;
    }

    /* Date Filter Styling */
    main .date {
        display: inline-block;
        background: var(--color-white);
        border-radius: var(--border-radius-1);
        margin-top: 1rem;
        padding: 0.5rem 1.6rem;
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
    }

    main .date:hover {
        box-shadow: none;
    }

    main .date form {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    main .date input[type='date'] {
        background: var(--color-light);
        color: var(--color-dark);
        border: none;
        padding: 0.5rem;
        border-radius: var(--border-radius-1);
        font-family: inherit;
        cursor: pointer;
    }
    
    main .date button {
        background: var(--color-primary);
        color: var(--color-white);
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: var(--border-radius-1);
        cursor: pointer;
        font-weight: 500;
        transition: all 300ms ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    main .date button:hover {
        background: var(--color-primary-variant);
        transform: translateY(-2px);
    }

    /* Insights Cards */
    main .insights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.6rem;
    }

    main .insights > div {
        background: var(--color-white);
        padding: var(--card-padding);
        border-radius: var(--card-border-radius);
        margin-top: 1rem;
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        border: 1px solid rgba(0,0,0,0.02);
    }

    main .insights > div:hover {
        box-shadow: none;
        transform: translateY(-5px);
    }

    main .insights > div span {
        background: var(--color-primary);
        padding: 0.5rem;
        border-radius: 50%;
        color: var(--color-white);
        font-size: 2rem;
    }

    main .insights > div.expenses span {
        background: var(--color-danger);
    }

    main .insights > div.income span {
        background: var(--color-success);
    }
    
    main .insights > div.pending span {
        background: var(--color-warning);
    }

    main .insights > div .middle {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    main .insights h3 {
        margin: 1rem 0 0.6rem;
        font-size: 1rem;
    }

    main .insights .progress {
        position: relative;
        width: 92px;
        height: 92px;
        border-radius: 50%;
    }

    main .insights svg {
        width: 7rem;
        height: 7rem;
    }

    main .insights svg circle {
        fill: none;
        stroke: var(--color-primary);
        stroke-width: 14;
        stroke-linecap: round;
        transform: translate(5px, 5px);
        stroke-dasharray: 110;
        stroke-dashoffset: 92;
    }

    main .insights .sales svg circle {
        stroke-dashoffset: -30;
        stroke-dasharray: 200;
    }

    main .insights .expenses svg circle {
        stroke-dashoffset: 20;
        stroke-dasharray: 80;
    }

    main .insights .income svg circle {
        stroke-dashoffset: 35;
        stroke-dasharray: 110;
    }

    main .insights .progress .number {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    main .insights small {
        margin-top: 1.3rem;
        display: block;
    }

    /* Charts */
    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }
    
    .chart-box {
        background: var(--color-white);
        padding: var(--card-padding);
        border-radius: var(--card-border-radius);
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .chart-box:hover {
        box-shadow: none;
        transform: translateY(-3px);
    }
    
    .chart-box h2 {
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
        color: var(--color-dark);
        font-weight: 600;
        text-align: center;
    }

    /* Recent Orders */
    main .recent-orders {
        margin-top: 3rem;
    }

    main .recent-orders h2 {
        margin-bottom: 1rem;
    }

    main .recent-orders table {
        background: var(--color-white);
        width: 100%;
        border-radius: var(--card-border-radius);
        padding: var(--card-padding);
        text-align: center;
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }

    main .recent-orders table:hover {
        box-shadow: none;
    }

    main table thead th {
        padding: 1rem;
        color: var(--color-dark);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    main table tbody td {
        height: 3.5rem;
        border-bottom: 1px solid var(--color-light);
        color: var(--color-dark-variant);
        background: var(--color-white);
    }
    
    main table tbody tr:hover td {
        background: var(--color-background);
        color: var(--color-dark);
        cursor: default;
    }

    main table tbody tr:last-child td {
        border: none;
    }

    main .recent-orders a {
        text-align: center;
        display: block;
        margin: 1.5rem auto;
        color: var(--color-primary);
        font-weight: 500;
        transition: all 300ms ease;
    }
    
    main .recent-orders a:hover {
        text-decoration: underline;
    }
    
    .text-muted { color: var(--color-info-dark); }
    .primary { color: var(--color-primary); }
    .danger { color: var(--color-danger); }
    .success { color: var(--color-success); }
    .warning { color: var(--color-warning); }
    
    /* Status Badges */
    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
        display: inline-block;
        min-width: 80px;
    }
    .status-badge.warning { background: #fff8e1; color: #ffc107; }
    .status-badge.success { background: #e8f5e9; color: #4caf50; }
    .status-badge.danger { background: #ffebee; color: #f44336; }
    .status-badge.primary { background: #e3f2fd; color: #2196f3; }
    .status-badge.info { background: #e0f7fa; color: #00bcd4; }
    .status-badge.secondary { background: #f3e5f5; color: #9c27b0; }
    .status-badge.dark { background: #eceff1; color: #607d8b; }

    /* Collapsible Sidebar Styles */
    aside {
        transition: all 300ms ease;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto; /* Allow scrolling within sidebar if content is too long */
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    aside::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    aside {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    aside.collapsed {
        width: 5rem;
    }

    aside.collapsed .logo {
        width: max-content;
        overflow: visible;
    }

    aside.collapsed .logo img {
        width: auto;
        max-width: none;
    }

    /* Dual Logo Logic */
    .logo-collapsed {
        display: none;
    }

    aside.collapsed .logo-full {
        display: none;
    }

    aside.collapsed .logo-collapsed {
        display: block;
    }



    aside.collapsed .logo h2 {
        display: none;
    }

    aside.collapsed .sidebar h3 {
        display: none;
    }

    aside.collapsed .sidebar a {
        display: flex;
        justify-content: center;
    }

    aside.collapsed .sidebar a:last-child {
        position: relative;
        margin-top: 1.8rem;
    }

    aside.collapsed .close {
        align-items: center;
    }

    main .insights small {
        margin-top: 1.3rem;
        display: block;
    }

    /* Charts */
    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .chart-box {
        background: var(--color-white);
        padding: 1.2rem;
        border-radius: var(--card-border-radius);
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .chart-box:hover {
        box-shadow: none;
        transform: translateY(-3px);
    }
    
    .chart-box h2 {
        margin-bottom: 0.8rem;
        font-size: 1.1rem;
        color: var(--color-dark);
        font-weight: 600;
        text-align: center;
    }

    /* Recent Orders */
    main .recent-orders {
        margin-top: 3rem;
    }

    main .recent-orders h2 {
        margin-bottom: 1rem;
    }

    main .recent-orders table {
        background: var(--color-white);
        width: 100%;
        border-radius: var(--card-border-radius);
        padding: var(--card-padding);
        text-align: center;
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }

    main .recent-orders table:hover {
        box-shadow: none;
    }

    main table thead th {
        padding: 1rem;
        color: var(--color-dark);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    main table tbody td {
        height: 3.5rem;
        border-bottom: 1px solid var(--color-light);
        color: var(--color-dark-variant);
        background: var(--color-white);
    }
    
    main table tbody tr:hover td {
        background: var(--color-background);
        color: var(--color-dark);
        cursor: default;
    }

    main table tbody tr:last-child td {
        border: none;
    }

    main .recent-orders a {
        text-align: center;
        display: block;
        margin: 1.5rem auto;
        color: var(--color-primary);
        font-weight: 500;
        transition: all 300ms ease;
    }
    
    main .recent-orders a:hover {
        text-decoration: underline;
    }
    
    .text-muted { color: var(--color-info-dark); }
    .primary { color: var(--color-primary); }
    .danger { color: var(--color-danger); }
    .success { color: var(--color-success); }
    .warning { color: var(--color-warning); }
    
    /* Status Badges */
    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
        display: inline-block;
        min-width: 80px;
    }
    .status-badge.warning { background: #fff8e1; color: #ffc107; }
    .status-badge.success { background: #e8f5e9; color: #4caf50; }
    .status-badge.danger { background: #ffebee; color: #f44336; }
    .status-badge.primary { background: #e3f2fd; color: #2196f3; }
    .status-badge.info { background: #e0f7fa; color: #00bcd4; }
    .status-badge.secondary { background: #f3e5f5; color: #9c27b0; }
    .status-badge.dark { background: #eceff1; color: #607d8b; }

    /* Collapsible Sidebar Styles */
    aside {
        transition: all 300ms ease;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto; /* Allow scrolling within sidebar if content is too long */
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    aside::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    aside {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    aside.collapsed {
        width: 5rem;
    }

    aside.collapsed .logo {
        width: max-content;
        overflow: visible;
    }

    aside.collapsed .logo img {
        width: auto;
        max-width: none;
    }

    /* Dual Logo Logic */
    .logo-collapsed {
        display: none;
    }

    aside.collapsed .logo-full {
        display: none;
    }

    aside.collapsed .logo-collapsed {
        display: block;
    }



    aside.collapsed .logo h2 {
        display: none;
    }

    aside.collapsed .sidebar h3 {
        display: none;
    }

    aside.collapsed .sidebar a {
        display: flex;
        justify-content: center;
    }

    aside.collapsed .sidebar a:last-child {
        position: relative;
        margin-top: 1.8rem;
    }

    aside.collapsed .close {
        display: none;
    }
    
    /* Adjust grid when sidebar is collapsed */
    body.sidebar-collapsed .container {
        grid-template-columns: 5rem auto;
    }

    /* Toggle Button */
    .sidebar-toggle {
        cursor: pointer;
        margin-left: 0.5rem;
        display: none; /* Hidden on mobile by default */
    }

    @media screen and (min-width: 768px) {
        .sidebar-toggle {
            display: block;
        }
    }
    
    /* Override grid for 2 columns */
    .container {
        grid-template-columns: 14rem auto;
    }
    
    @media screen and (max-width: 1200px) {
        .container {
            grid-template-columns: 7rem auto;
        }
    }
    
    @media screen and (max-width: 768px) {
        .container {
            grid-template-columns: 1fr;
        }
    }

</style>
@endpush

<div class="container">
  @include('admin.partials.sidebar', ['active' => 'dashboard'])

  <main>
    <h1>Dashboard</h1>

    <div class="date">
        <form action="{{ route('admin.dashboard') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
            <input type="date" name="start_date" value="{{ $startDate }}">
            <span>to</span>
            <input type="date" name="end_date" value="{{ $endDate }}">
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="insights">
        <!-- Total Orders -->
        <div class="sales">
            <span class="material-icons-sharp">shopping_cart</span>
            <div class="middle">
                <div class="left">
                    <h3>Tổng Đơn (Tháng)</h3>
                    <h1>{{ $totalOrdersMonth }}</h1>
                </div>
            </div>
            <small class="text-muted">Day: {{ $totalOrdersDay }} | Week: {{ $totalOrdersWeek }}</small>
        </div>

        <!-- Revenue -->
        <div class="income">
            <span class="material-icons-sharp">stacked_line_chart</span>
            <div class="middle">
                <div class="left">
                    <h3>Doanh thu (Tháng)</h3>
                    <h1>{{ number_format($revenueMonth) }} đ</h1>
                </div>
            </div>
            <small class="text-muted">Day: {{ number_format($revenueDay) }} | Week: {{ number_format($revenueWeek) }}</small>
        </div>

        <!-- Pending Orders -->
        <div class="pending">
            <span class="material-icons-sharp">pending</span>
            <div class="middle">
                <div class="left">
                    <h3>Đơn Chờ Xác Nhận</h3>
                    <h1>{{ $pendingOrders }}</h1>
                </div>
            </div>
            <small class="text-muted">Action needed</small>
        </div>
        
        <!-- In Progress -->
        <div class="sales">
             <span class="material-icons-sharp">local_shipping</span>
            <div class="middle">
                <div class="left">
                    <h3>Đơn Đang Thực Hiện</h3>
                    <h1>{{ $inProgressOrders }}</h1>
                </div>
            </div>
            <small class="text-muted">On the way</small>
        </div>

         <!-- Working Staff -->
        <div class="expenses">
             <span class="material-icons-sharp">engineering</span>
            <div class="middle">
                <div class="left">
                    <h3>Tổng Nhân Viên</h3>
                    <h1>{{ $workingStaff }}</h1>
                </div>
            </div>
            <small class="text-muted">Active staff</small>
        </div>
        
    </div>

    <!-- Charts Section -->
    <div class="charts-container">
        <div class="chart-box">
            <h2>Đơn hàng theo ngày</h2>
            <canvas id="ordersChart"></canvas>
        </div>
        <div class="chart-box">
            <h2>Phân phối dịch vụ</h2>
            <canvas id="servicesChart"></canvas>
        </div>
        <div class="chart-box">
            <h2>Thống kê loại đơn</h2>
            <canvas id="orderTypesChart"></canvas>
        </div>
    </div>

    <div class="recent-orders">
        <h2>Đơn hàng gần đây</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Dịch vụ</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentOrders as $order)
                @php
                    $statusClass = 'primary';
                    if($order->TrangThaiDon == 'waiting_confirmation') $statusClass = 'warning';
                    elseif($order->TrangThaiDon == 'cancelled') $statusClass = 'danger';
                    elseif($order->TrangThaiDon == 'done') $statusClass = 'success';
                    elseif($order->TrangThaiDon == 'shipping') $statusClass = 'info';
                @endphp
                <tr>
                    <td>#{{ $order->ID_DD }}</td>
                    <td>{{ $order->khachHang->Ten_KH ?? 'N/A' }}</td>
                    <td>{{ $order->dichVu->TenDV ?? 'N/A' }}</td>
                    <td>{{ number_format($order->TongTienSauGiam) }} đ</td>
                    <td>
                        <span class="status-badge {{ $statusClass }}">
                            {{ $deliverStatusLabels[$order->TrangThaiDon] ?? $order->TrangThaiDon }}
                        </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <a href="#">Show All</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.font.family = "'Poppins', sans-serif";
            Chart.defaults.color = '#677483';
            
            // Orders Chart
            const ctxOrders = document.getElementById('ordersChart').getContext('2d');
            new Chart(ctxOrders, {
                type: 'line',
                data: {
                    labels: @json($ordersByDayLabels),
                    datasets: [{
                        label: 'Đơn hàng',
                        data: @json($ordersByDayValues),
                        borderColor: '#7380ec',
                        backgroundColor: 'rgba(115, 128, 236, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#7380ec',
                        pointBorderColor: '#fff',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: { 
                    responsive: true, 
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#363949',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                borderDash: [5, 5],
                                color: 'rgba(132, 139, 200, 0.18)'
                            },
                            ticks: { padding: 10 }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { padding: 10 }
                        }
                    }
                }
            });

            // Services Chart
            const ctxServices = document.getElementById('servicesChart').getContext('2d');
            new Chart(ctxServices, {
                type: 'doughnut',
                data: {
                    labels: @json($serviceDistributionLabels),
                    datasets: [{
                        data: @json($serviceDistributionValues),
                        backgroundColor: [
                            '#ff7782', '#7380ec', '#41f1b6', '#ffbb55', '#36a2eb'
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: { 
                    responsive: true,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 20 }
                        }
                    }
                }
            });

            // Order Types Chart
            const ctxOrderTypes = document.getElementById('orderTypesChart').getContext('2d');
            new Chart(ctxOrderTypes, {
                type: 'pie',
                data: {
                    labels: @json($orderTypeLabels),
                    datasets: [{
                        data: @json($orderTypeValues),
                        backgroundColor: ['#ffbb55', '#7380ec'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: { 
                    responsive: true,
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 20 }
                        } 
                    }
                }
            });

            // Sidebar Toggle Logic
            const logoToggle = document.getElementById('logo-toggle');
            const aside = document.querySelector('aside');
            const body = document.body;

            if (logoToggle) {
                logoToggle.addEventListener('click', () => {
                    aside.classList.toggle('collapsed');
                    body.classList.toggle('sidebar-collapsed');
                });
            }
        });
    </script>
  </main>
</div>
@endsection
