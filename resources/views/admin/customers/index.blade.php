@extends('layouts.admin')

@section('title', 'Quản lý khách hàng')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
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
        min-width: 1000px;
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
        white-space: nowrap;
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
        height: 48px;
        white-space: nowrap;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }

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
        </div>

        <div class="card-container">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Họ và Tên</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td style="font-weight: 600;">{{ $customer->Ten_KH }}</td>
                            <td>{{ $customer->SDT }}</td>
                            <td>{{ $customer->Email }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 2rem;">
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
