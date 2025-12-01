@extends('layouts.admin')

@section('title', 'Thanh Toán Lương Nhân Viên')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --font-family: 'Inter', sans-serif;
        --color-bg-light: #F5F7FA;
        --color-white: #FFFFFF;
        --color-primary-orange: #FF7B29;
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
        min-width: 0;
        width: 100%;
    }

    /* Summary Cards */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .summary-card {
        background: var(--color-white);
        padding: 1.5rem;
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .summary-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .summary-card-icon.blue {
        background: #E0F2FE;
        color: #0369A1;
    }

    .summary-card-icon.green {
        background: #DCFCE7;
        color: #15803D;
    }

    .summary-card-icon.orange {
        background: #FEF3C7;
        color: #FF7B29;
    }

    .summary-card-content h3 {
        margin: 0;
        font-size: 0.875rem;
        color: var(--color-text-gray);
        font-weight: 500;
    }

    .summary-card-content p {
        margin: 0.25rem 0 0 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--color-text-dark);
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
            margin-top: 1rem;
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
        text-decoration: none;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-green {
        background: #10B981;
    }

    .btn-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
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

    tbody tr.eligible {
        background-color: #F0FDF4;
    }

    tbody tr.eligible:hover {
        background-color: #E8FCEF;
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

    .col-bold {
        font-weight: 600;
        color: var(--color-text-dark);
    }

    /* Status Badges */
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-badge.success {
        background: #DCFCE7;
        color: #15803D;
    }

    .status-badge.secondary {
        background: #F3F4F6;
        color: #6B7280;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }

    .alert {
        padding: 1rem;
        border-radius: var(--radius-btn);
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .alert-success {
        background: #DCFCE7;
        color: #15803D;
        border: 1px solid #BBF7D0;
    }

    .alert-error {
        background: #FEE2E2;
        color: #B91C1C;
        border: 1px solid #FECACA;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s;
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: var(--color-white);
        padding: 2rem;
        border-radius: var(--radius-card);
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .modal-header .material-icons-sharp {
        font-size: 3rem;
        color: #F59E0B;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        color: var(--color-text-dark);
    }

    .modal-body {
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .modal-info {
        background: #FEF3C7;
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
    }

    .modal-info p {
        margin: 0.5rem 0;
        font-weight: 600;
    }

    .modal-footer {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .btn-cancel {
        background: #F3F4F6;
        color: var(--color-text-dark);
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-btn);
        border: none;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-cancel:hover {
        background: #E5E7EB;
    }

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
    @include('admin.partials.sidebar', ['active' => 'employees'])

    <main>
        <h1>Thanh Toán Lương Nhân Viên</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-card-icon blue">
                    <span class="material-icons-sharp">group</span>
                </div>
                <div class="summary-card-content">
                    <h3>Tổng số nhân viên</h3>
                    <p>{{ $employees->total() }}</p>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-icon green">
                    <span class="material-icons-sharp">check_circle</span>
                </div>
                <div class="summary-card-content">
                    <h3>Nhân viên đủ điều kiện</h3>
                    <p>{{ $totalEligible }}</p>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-icon orange">
                    <span class="material-icons-sharp">payments</span>
                </div>
                <div class="summary-card-content">
                    <h3>Tổng tiền lương</h3>
                    <p>{{ number_format($totalSalary) }}đ</p>
                </div>
            </div>
        </div>

        <div class="filter-bar">
            <form action="{{ route('admin.employees.salary') }}" method="GET" class="filter-form">
                <div class="search-group">
                    <span class="material-icons-sharp search-icon">search</span>
                    <input type="text" name="q" placeholder="Tìm kiếm theo tên, SĐT, Email..." value="{{ request('q') }}">
                </div>
                <button type="submit" class="btn-primary">Lọc</button>
            </form>

            <div class="btn-actions">
                <a href="#" onclick="exportSalary(event)" class="btn-primary btn-green">
                    <span class="material-icons-sharp">file_download</span>
                    Xuất Excel
                </a>
                @if($totalEligible > 0)
                <button onclick="showConfirmModal()" class="btn-primary">
                    <span class="material-icons-sharp">payments</span>
                    Trả Lương Tất Cả
                </button>
                @endif
            </div>
        </div>

        <div class="card-container">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Họ và Tên</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Số dư hiện tại</th>
                            <th>Lương nhận</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaryData as $data)
                        <tr class="{{ $data['isEligible'] ? 'eligible' : '' }}">
                            <td class="col-bold">{{ $data['employee']->Ten_NV }}</td>
                            <td>{{ $data['employee']->SDT }}</td>
                            <td>{{ $data['employee']->Email }}</td>
                            <td>{{ number_format($data['employee']->SoDu ?? 0) }} đ</td>
                            <td style="font-weight: bold; color: {{ $data['isEligible'] ? '#15803D' : '#6B7280' }};">
                                {{ number_format($data['isEligible'] ? $data['salary'] : 0) }} đ
                            </td>
                            <td>
                                <span class="status-badge {{ $data['isEligible'] ? 'success' : 'secondary' }}">
                                    {{ $data['isEligible'] ? 'Đủ điều kiện' : 'Không đủ điều kiện' }}
                                </span>
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
                {{ $employees->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </main>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="material-icons-sharp">warning</span>
            <h2>Xác Nhận Thanh Toán Lương</h2>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn thanh toán lương cho tất cả nhân viên đủ điều kiện?</p>
            <div class="modal-info">
                <p>Số nhân viên: <strong>{{ $totalEligible }}</strong></p>
                <p>Tổng số tiền: <strong>{{ number_format($totalSalary) }}đ</strong></p>
            </div>
            <p style="margin-top: 1rem; color: #B91C1C;">
                <strong>Lưu ý:</strong> Hành động này không thể hoàn tác. Số dư của tất cả nhân viên đủ điều kiện sẽ được đặt lại về 400,000đ.
            </p>
        </div>
        <div class="modal-footer">
            <button onclick="hideConfirmModal()" class="btn-cancel">Hủy</button>
            <form id="paymentForm" action="{{ route('admin.employees.salary.process') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-primary">Xác Nhận Thanh Toán</button>
            </form>
        </div>
    </div>
</div>

<script>
    function exportSalary(e) {
        e.preventDefault();
        const q = document.querySelector('input[name="q"]').value;
        const url = `{{ route('admin.employees.salary.export') }}?q=${q}`;
        window.location.href = url;
    }

    function showConfirmModal() {
        document.getElementById('confirmModal').classList.add('show');
    }

    function hideConfirmModal() {
        document.getElementById('confirmModal').classList.remove('show');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('confirmModal');
        if (event.target === modal) {
            hideConfirmModal();
        }
    }
</script>
@endsection
