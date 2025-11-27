@extends('layouts.admin')

@section('title', 'Quản lý khách hàng')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --font-family: 'Inter', sans-serif;
        --color-bg-light: #F5F7FA;
        --color-white: #FFFFFF;
        --color-primary-orange: #FF7B29; /* bTaskee Orange approx */
        --color-text-dark: #1F2937;
        --color-text-gray: #6B7280;
        --color-border: #E5E7EB;
        --color-header-bg: #F9FAFB;
        --shadow-card: 0 4px 20px rgba(0,0,0,0.06);
        --radius-card: 16px;
        --radius-input: 12px;
        --radius-btn: 12px;
    }

    body {
        font-family: var(--font-family);
        background-color: var(--color-bg-light);
        color: var(--color-text-dark);
    }

    main {
        min-width: 0; /* Prevent grid blowout for scrolling */
        width: 100%;
    }

    /* Filter Bar */
    .filter-bar {
        background: var(--color-white);
        padding: 1.5rem;
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        margin-bottom: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .filter-bar {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            margin-top:1rem;
        }
    }

    .filter-form {
        display: flex;
        gap: 1rem;
        flex: 1;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-group {
        position: relative;
        flex: 1;
        min-width: 250px;
    }

    .search-group input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border-radius: var(--radius-input);
        border: 1px solid var(--color-border);
        background: var(--color-bg-light);
        color: var(--color-text-dark);
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }

    .search-group input:focus {
        border-color: var(--color-primary-orange);
        outline: none;
        background: var(--color-white);
    }

    .search-group .search-icon {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--color-text-gray);
        font-size: 1.2rem;
    }

    .filter-select {
        padding: 0.75rem 1rem;
        border-radius: var(--radius-input);
        border: 1px solid var(--color-border);
        background: var(--color-white);
        color: var(--color-text-dark);
        cursor: pointer;
        min-width: 180px;
    }

    .btn-primary {
        background: var(--color-primary-orange);
        color: var(--color-white);
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-btn);
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: opacity 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-sync {
        background: var(--color-white);
        color: var(--color-text-dark);
        border: 1px solid var(--color-border);
    }
    
    .btn-sync:hover {
        background: var(--color-bg-light);
    }

    /* Card Container */
    .card-container {
        background: var(--color-white);
        border-radius: var(--radius-card);
        padding: 1.5rem;
        box-shadow: var(--shadow-card);
        width: 100%;
    }

    /* Table */
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1200px; /* allow full width but prevent squish */
    }

    thead th {
        background: var(--color-header-bg);
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: var(--color-text-gray);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--color-border);
        white-space: nowrap; /* Prevent header wrap */
    }

    thead th:first-child {
        border-top-left-radius: 8px;
    }

    thead th:last-child {
        border-top-right-radius: 8px;
    }

    tbody tr {
        transition: background-color 0.2s;
    }

    tbody tr:hover {
        background-color: #FAFAFA;
    }

    tbody td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--color-border);
        color: var(--color-text-dark);
        vertical-align: middle;
        font-size: 0.95rem;
        height: 48px; /* Min height */
        white-space: nowrap; /* Prevent cell wrap to force scroll */
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    /* Column Specifics */
    .col-bold {
        font-weight: 600;
        color: var(--color-text-dark);
    }

    .col-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: help;
    }

    /* Status Badges */
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }
    .status-badge.primary { background: #E0F2FE; color: #0369A1; } /* Blue */
    .status-badge.success { background: #DCFCE7; color: #15803D; } /* Green */
    .status-badge.danger { background: #FEE2E2; color: #B91C1C; } /* Red */
    .status-badge.warning { background: #FEF3C7; color: #B45309; } /* Yellow */

    /* Pagination */
    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }
    
    /* Tooltip container */
    .tooltip-container {
        position: relative;
        display: inline-block;
    }
    
    .tooltip-text {
        visibility: hidden;
        width: 250px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -125px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 0.8rem;
        font-weight: normal;
        white-space: normal;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .alert {
        padding: 1rem;
        border-radius: var(--radius-btn);
        margin-bottom: 1.5rem;
        font-weight: 500;
    }
    .alert-success { background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0; }
    .alert-error { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
    .alert-warning { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }

    /* Override container for full width */
    .container {
        max-width: 100% !important;
        padding-right: 1.5rem;
    }

    @media screen and (min-width: 1201px) {
        .container {
            grid-template-columns: 14rem auto !important;
        }
    }

    @media screen and (max-width: 1200px) and (min-width: 769px) {
        .container {
            grid-template-columns: 7rem auto !important;
        }
    }
</style>
@endpush

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'customers'])

    <main>
        <h1>Quản lý khách hàng</h1>

        <div class="filter-bar">
            <form action="{{ route('admin.customers.index') }}" method="GET" class="filter-form">
                <div class="search-group">
                    <span class="material-icons-sharp search-icon">search</span>
                    <input type="text" name="q" placeholder="Tìm kiếm theo tên, SĐT, Email..." value="{{ request('q') }}">
                </div>
                <button type="submit" class="btn-primary">Lọc</button>
            </form>
            <a href="{{ route('admin.customers.export') }}" class="btn-primary btn-sync" style="text-decoration: none;">
                <span class="material-icons-sharp">download</span>
                Xuất Excel
            </a>
        </div>

        <div class="card-container">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Họ và Tên</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Tổng chi tiêu</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td class="col-bold">{{ $customer->Ten_KH }}</td>
                            <td>{{ $customer->SDT }}</td>
                            <td>{{ $customer->Email }}</td>
                            <td>{{ number_format($customer->donDats->sum('TongTienSauGiam')) }} đ</td>
                            <td>
                                @php
                                    $status = optional($customer->taiKhoan)->TrangThaiTK;
                                    $badgeClass = match ($status) {
                                        'active' => 'success',
                                        'banned' => 'danger',
                                        default => 'warning',
                                    };
                                    $statusText = match ($status) {
                                        'active' => 'Hoạt động',
                                        'banned' => 'Đã khóa',
                                        default => 'Chưa kích hoạt',
                                    };
                                    
                                    $btnIcon = $status === 'active' ? 'lock' : 'lock_open';
                                    $btnTitle = $status === 'active' ? 'Khóa tài khoản' : 'Kích hoạt tài khoản';
                                @endphp
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ $statusText }}
                                    </span>
                                    @if($customer->taiKhoan)
                                    <form action="{{ route('admin.customers.updateStatus', $customer) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0; color: var(--color-text-gray);" title="{{ $btnTitle }}">
                                            <span class="material-icons-sharp" style="font-size: 1.2rem;">{{ $btnIcon }}</span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                <span class="material-icons-sharp" style="font-size: 3rem; color: var(--color-text-gray); display: block; margin-bottom: 0.5rem;">inbox</span>
                                Không có dữ liệu.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $customers->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</div>
@endsection
