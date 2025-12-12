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
        align-items: center; /* Align items vertically */
        margin-top: 2rem;
        gap: 0.5rem;
    }
    
    .pagination .page-item {
        display: flex; /* Ensure items are flex containers */
        align-items: center;
        margin: 0; /* Reset margins */
    }

    .pagination .page-item .page-link {
        padding: 0.5rem 1rem;
        border-radius: 0.4rem;
        background: var(--color-white);
        color: var(--color-dark);
        text-decoration: none;
        box-shadow: var(--box-shadow);
        border: 1px solid transparent; /* Ensure consistent border */
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%; /* consistent height */
        min-width: 2.5rem; /* Minimum width for square-ish look */
    }

    .pagination .page-item.active .page-link {
        background: var(--color-primary);
        color: var(--color-white);
        border-color: var(--color-primary);
    }
    
    .pagination .page-item.disabled .page-link {
        background: var(--color-light);
        color: var(--color-dark-variant);
        pointer-events: none;
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
            <a href="{{ route('admin.orders.index', array_merge(request()->only(['status', 'date_from', 'date_to', 'search']), ['type' => 'hour'])) }}" class="order-tab {{ $currentType == 'hour' ? 'active' : '' }}">
                Giúp việc theo giờ
            </a>
            <a href="{{ route('admin.orders.index', array_merge(request()->only(['status', 'date_from', 'date_to', 'search']), ['type' => 'month'])) }}" class="order-tab {{ $currentType == 'month' ? 'active' : '' }}">
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

        <div class="recent-orders">
            @include('admin.orders.table')
        </div>

        <script>
            // AJAX Pagination and Filtering
            document.addEventListener('DOMContentLoaded', function() {
                // Handle Pagination Clicks
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.pagination .page-link')) {
                        e.preventDefault();
                        const url = e.target.closest('.page-link').getAttribute('href');
                        if (url) {
                            fetchOrders(url);
                        }
                    }
                });

                // Handle Filter Form Submit
                const filterForm = document.querySelector('.filter-form');
                if (filterForm) {
                    filterForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Validate dates
                        const dateFromVal = document.querySelector('input[name="date_from"]').value;
                        const dateToVal = document.querySelector('input[name="date_to"]').value;

                        if (dateFromVal && dateToVal) {
                            const start = new Date(dateFromVal);
                            const end = new Date(dateToVal);

                            if (end < start) {
                                alert('Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu!');
                                return;
                            }
                        }

                        // Build URL with params
                        const formData = new FormData(this);
                        const params = new URLSearchParams(formData);
                        const url = "{{ route('admin.orders.index') }}?" + params.toString();
                        
                        fetchOrders(url);
                    });
                }
            });

            function fetchOrders(url) {
                // Show loading state if desired (optional)
                const container = document.querySelector('.recent-orders');
                container.style.opacity = '0.5';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                    
                    // Update URL in browser address bar without reload
                    window.history.pushState({}, '', url);
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.style.opacity = '1';
                    alert('Có lỗi xảy ra khi tải dữ liệu.');
                });
            }

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
    </main>

    </main>
</div>
@endsection
