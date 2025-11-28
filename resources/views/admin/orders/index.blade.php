@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
@push('styles')
<style>
    .container {
        grid-template-columns: 14rem auto !important;
    }

    .order-tabs {
        background: var(--color-white);
        width: 100%;
        border-radius: var(--card-border-radius);
        padding: var(--card-padding);
        box-shadow: var(--box-shadow);
        transition: all 300ms ease;
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        margin-top: 2rem;
    }

    .order-tabs:hover {
        box-shadow: none;
    }

    .order-tab {
        padding: 0.8rem 1.6rem;
        border-radius: var(--border-radius-1);
        color: var(--color-dark);
        font-weight: 600;
        cursor: pointer;
        transition: all 300ms ease;
        text-decoration: none;
        border: 1px solid var(--color-info-light);
    }

    .order-tab.active {
        background: var(--color-primary);
        color: var(--color-white);
        border-color: var(--color-primary);
    }

    .order-tab:hover:not(.active) {
        background: var(--color-light);
    }

    .recent-orders {
        margin-top: 2rem;
    }

    .recent-orders table {
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

    .recent-orders table:hover {
        box-shadow: none;
    }

    table thead th {
        padding: 1rem;
        color: var(--color-dark);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    table tbody td {
        height: 3.5rem;
        border-bottom: 1px solid var(--color-light);
        color: var(--color-dark-variant);
        background: var(--color-white);
    }

    table tbody tr:hover td {
        background: var(--color-background);
        color: var(--color-dark);
        cursor: default;
    }

    table tbody tr:last-child td {
        border: none;
    }

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

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
        gap: 0.5rem;
    }
    
    .pagination .page-item .page-link {
        padding: 0.5rem 1rem;
        border-radius: 0.4rem;
        background: var(--color-white);
        color: var(--color-dark);
        text-decoration: none;
        box-shadow: var(--box-shadow);
    }

    .pagination .page-item.active .page-link {
        background: var(--color-primary);
        color: var(--color-white);
    }

    /* Filter Form */
    .filter-form {
        background: var(--color-white);
        padding: 1.5rem;
        border-radius: var(--card-border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        align-items: flex-end;
    }

    .filter-form .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        flex: 1;
        min-width: 200px;
    }

    .filter-form label {
        font-weight: 600;
        color: var(--color-dark);
    }

    .filter-form .form-control {
        padding: 0.6rem 1rem;
        border-radius: var(--border-radius-1);
        border: 1px solid var(--color-info-light);
        background: var(--color-white);
        color: var(--color-dark);
        width: 100%;
    }

    .filter-form .form-actions {
        display: flex;
        gap: 1rem;
        padding-bottom: 2px;
    }

    .btn-primary {
        background: var(--color-primary);
        color: var(--color-white);
        padding: 0.6rem 2rem;
        border-radius: var(--border-radius-1);
        cursor: pointer;
        border: none;
        font-weight: 600;
        transition: all 300ms ease;
    }

    .btn-secondary {
        background: var(--color-light);
        color: var(--color-dark);
        padding: 0.6rem 2rem;
        border-radius: var(--border-radius-1);
        cursor: pointer;
        border: none;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 300ms ease;
    }

    .btn-primary:hover {
        background: #5a6efa;
    }

    .btn-secondary:hover {
        background: var(--color-dark);
        color: var(--color-white);
    }
</style>
@endpush

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'orders'])

    <main>
        <h1>Quản lý đơn hàng</h1>

        <div class="order-tabs">
            <a href="{{ route('admin.orders.index', ['type' => 'hour']) }}" class="order-tab {{ $currentType == 'hour' ? 'active' : '' }}">
                Giúp việc theo giờ
            </a>
            <a href="{{ route('admin.orders.index', ['type' => 'month']) }}" class="order-tab {{ $currentType == 'month' ? 'active' : '' }}">
                Giúp việc theo tháng
            </a>
        </div>

        <form action="{{ route('admin.orders.index') }}" method="GET" class="filter-form">
            <input type="hidden" name="type" value="{{ $currentType }}">
            
            <div class="form-group">
                <label>Từ ngày</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Đến ngày</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="finding_staff" {{ $status == 'finding_staff' ? 'selected' : '' }}>Đang tìm NV</option>
                    <option value="assigned" {{ $status == 'assigned' ? 'selected' : '' }}>Đã có NV</option>
                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Khách hàng</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Tên khách hàng..." class="form-control">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="{{ route('admin.orders.index', ['type' => $currentType]) }}" class="btn-secondary">Đặt lại</a>
                <a href="#" onclick="exportOrders(event)" class="btn-primary" style="background: #10B981; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons-sharp">file_download</span>
                    Xuất Excel
                </a>
            </div>
        </form>

        <script>
            document.querySelector('.filter-form').addEventListener('submit', function(e) {
                const dateFromVal = document.querySelector('input[name="date_from"]').value;
                const dateToVal = document.querySelector('input[name="date_to"]').value;

                if (dateFromVal && dateToVal) {
                    const start = new Date(dateFromVal);
                    const end = new Date(dateToVal);

                    if (end < start) {
                        e.preventDefault();
                        alert('Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu!');
                    }
                }
            });

            function exportOrders(e) {
                e.preventDefault();
                const type = '{{ $currentType }}';
                const dateFrom = document.querySelector('input[name="date_from"]').value;
                const dateTo = document.querySelector('input[name="date_to"]').value;
                const status = document.querySelector('select[name="status"]').value;
                const search = document.querySelector('input[name="search"]').value;

                if (dateFrom && dateTo && new Date(dateTo) < new Date(dateFrom)) {
                    alert('Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu!');
                    return;
                }

                const url = `{{ route('admin.orders.export') }}?type=${type}&date_from=${dateFrom}&date_to=${dateTo}&status=${status}&search=${search}`;
                window.location.href = url;
            }
        </script>

        <div class="recent-orders">
            <h2>Danh sách đơn hàng ({{ $currentType == 'hour' ? 'Theo giờ' : 'Theo tháng' }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Dịch vụ</th>
                        <th>Nhân viên</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    @php
                        $statusClass = 'primary';
                        $statusLabel = $order->TrangThaiDon;
                        
                        // Map status to class and label
                        switch($order->TrangThaiDon) {
                            case 'waiting_confirmation':
                                $statusClass = 'warning';
                                $statusLabel = 'Chờ xác nhận';
                                break;
                            case 'finding_staff':
                                $statusClass = 'info';
                                $statusLabel = 'Đang tìm NV';
                                break;
                            case 'assigned':
                                $statusClass = 'primary';
                                $statusLabel = 'Đã có NV';
                                break;
                            case 'done':
                                $statusClass = 'success';
                                $statusLabel = 'Hoàn thành';
                                break;
                            case 'cancelled':
                                $statusClass = 'danger';
                                $statusLabel = 'Đã hủy';
                                break;
                        }
                    @endphp
                    <tr>
                        <td>#{{ $order->ID_DD }}</td>
                        <td>{{ $order->khachHang->Ten_KH ?? 'N/A' }}</td>
                        <td>{{ $order->dichVu->TenDV ?? 'N/A' }}</td>
                        <td>{{ $order->nhanVien->Ten_NV ?? 'Chưa có' }}</td>
                        <td>{{ number_format($order->TongTienSauGiam) }} đ</td>
                        <td>
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order->ID_DD) }}" class="primary">Chi tiết</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">Không có đơn hàng nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="d-flex justify-content-center">
                {{ $orders->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>

    </main>
</div>
@endsection
