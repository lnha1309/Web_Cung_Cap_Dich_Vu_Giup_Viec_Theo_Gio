@extends('layouts.admin')

@section('title', 'Quản lý nhân viên')

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

    /* Rating Stars */
    .rating-stars {
        color: #FFB800;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .rating-value {
        color: var(--color-text-dark);
        font-weight: 600;
        margin-left: 0.25rem;
    }

    .btn-view-reviews {
        background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
        color: white;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.75rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: opacity 0.2s;
    }

    .btn-view-reviews:hover {
        opacity: 0.9;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: var(--color-white);
        border-radius: var(--radius-card);
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--color-text-dark);
    }

    .modal-close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--color-text-gray);
        font-size: 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        max-height: 60vh;
    }

    .review-summary {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: var(--color-bg-light);
        border-radius: 12px;
    }

    .review-summary-score {
        font-size: 2rem;
        font-weight: 700;
        color: var(--color-primary-orange);
    }

    .review-summary-details {
        flex: 1;
    }

    .review-item {
        padding: 1rem;
        border: 1px solid var(--color-border);
        border-radius: 12px;
        margin-bottom: 1rem;
    }

    .review-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .review-item-customer {
        font-weight: 600;
        color: var(--color-text-dark);
    }

    .review-item-date {
        font-size: 0.8rem;
        color: var(--color-text-gray);
    }

    .review-item-rating {
        color: #FFB800;
        margin-bottom: 0.5rem;
    }

    .review-item-comment {
        color: var(--color-text-dark);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .no-reviews {
        text-align: center;
        color: var(--color-text-gray);
        padding: 2rem;
    }

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
    @include('admin.partials.sidebar', ['active' => 'employees'])

    <main>
        <h1>Quản lý nhân viên</h1>

        <div class="filter-bar">
            <form action="{{ route('admin.employees.index') }}" method="GET" class="filter-form">
                <div class="search-group">
                    <span class="material-icons-sharp search-icon">search</span>
                    <input type="text" name="q" placeholder="Tìm kiếm theo tên, SĐT, Email..." value="{{ request('q') }}">
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="filter-select" required>
                    <span style="color: var(--color-text-gray);">-</span>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="filter-select" required>
                </div>
                <button type="submit" class="btn-primary">Lọc</button>
                <a href="{{ route('admin.employees.salary') }}" class="btn-primary" style="background: linear-gradient(135deg, #FF7B29 0%, #FF9F5A 100%); text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons-sharp">payments</span>
                    Thanh toán lương
                </a>
                <a href="#" onclick="exportRevenue(event)" class="btn-primary" style="background: #10B981; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons-sharp">file_download</span>
                    Xuất Excel
                </a>
            </form>
        </div>

        <script>
            document.querySelector('.filter-form').addEventListener('submit', function(e) {
                const startDate = new Date(document.getElementById('start_date').value);
                const endDate = new Date(document.getElementById('end_date').value);

                if (endDate < startDate) {
                    e.preventDefault();
                    alert('Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu!');
                }
            });

            function exportRevenue(e) {
                e.preventDefault();
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const q = document.querySelector('input[name="q"]').value;
                
                // Validate dates before export
                if (new Date(endDate) < new Date(startDate)) {
                    alert('Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu!');
                    return;
                }

                const url = `{{ route('admin.employees.export-revenue') }}?start_date=${startDate}&end_date=${endDate}&q=${q}`;
                window.location.href = url;
            }
        </script>

        <div class="card-container">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Họ và Tên</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Khu vực</th>
                            <th>Số dư</th>
                            <th>Doanh thu ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m') }})</th>
                            <th>Đánh giá</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="col-bold">{{ $employee->Ten_NV }}</td>
                            <td>{{ $employee->SDT }}</td>
                            <td>{{ $employee->Email }}</td>
                            <td>{{ $employee->KhuVucLamViec }}</td>
                            <td>{{ number_format($employee->SoDu) }} đ</td>
                            <td style="font-weight: bold; color: var(--color-primary-orange);">
                                {{ number_format($employeeRevenues[$employee->ID_NV] ?? 0) }} đ
                            </td>
                            <td>
                                @php
                                    $avgRating = $employee->danhGias->avg('Diem') ?? 0;
                                    $totalReviews = $employee->danhGias->count();
                                @endphp
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <div class="rating-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= round($avgRating))
                                                <span class="material-icons-sharp" style="font-size: 1rem;">star</span>
                                            @else
                                                <span class="material-icons-sharp" style="font-size: 1rem; color: #D1D5DB;">star</span>
                                            @endif
                                        @endfor
                                        <span class="rating-value">{{ number_format($avgRating, 1) }}</span>
                                        <span style="color: var(--color-text-gray); font-size: 0.8rem;">({{ $totalReviews }})</span>
                                    </div>
                                    @if($totalReviews > 0)
                                    <button class="btn-view-reviews" onclick="viewReviews('{{ $employee->ID_NV }}')">
                                        <span class="material-icons-sharp" style="font-size: 0.9rem;">visibility</span>
                                        Xem chi tiết
                                    </button>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $status = optional($employee->taiKhoan)->TrangThaiTK;
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
                                    @if($employee->taiKhoan)
                                    <form action="{{ route('admin.employees.updateStatus', $employee) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        @if($status === 'active')
                                        <button type="submit" style="background: #FEE2E2; color: #B91C1C; border: none; cursor: pointer; padding: 0.35rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600;">
                                            Khóa tài khoản
                                        </button>
                                        @else
                                        <button type="submit" style="background: #DCFCE7; color: #15803D; border: none; cursor: pointer; padding: 0.35rem 0.75rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 600;">
                                            Mở khóa tài khoản
                                        </button>
                                        @endif
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
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

<!-- Reviews Modal -->
<div class="modal-overlay" id="reviewsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Đánh giá nhân viên</h3>
            <button class="modal-close" onclick="closeModal()">
                <span class="material-icons-sharp">close</span>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be loaded via JavaScript -->
        </div>
    </div>
</div>

<script>
    let currentEmployeeId = null;
    
    function viewReviews(employeeId) {
        currentEmployeeId = employeeId;
        const modal = document.getElementById('reviewsModal');
        const modalBody = document.getElementById('modalBody');
        const modalTitle = document.getElementById('modalTitle');
        
        modalBody.innerHTML = '<div style="text-align: center; padding: 2rem;"><span class="material-icons-sharp" style="animation: spin 1s linear infinite;">sync</span> Đang tải...</div>';
        modal.classList.add('active');
        
        loadReviews(employeeId);
    }
    
    function loadReviews(employeeId) {
        const modalBody = document.getElementById('modalBody');
        const modalTitle = document.getElementById('modalTitle');
        
        fetch(`/admin/employees/${employeeId}/reviews`)
            .then(response => response.json())
            .then(data => {
                modalTitle.textContent = `Đánh giá nhân viên: ${data.employee.name}`;
                
                let html = `
                    <div class="review-summary">
                        <div class="review-summary-score">${data.avgRating ? data.avgRating.toFixed(1) : '0.0'}</div>
                        <div class="review-summary-details">
                            <div class="rating-stars">
                                ${generateStars(data.avgRating || 0)}
                            </div>
                            <div style="color: var(--color-text-gray); margin-top: 0.25rem;">${data.totalReviews} đánh giá</div>
                        </div>
                    </div>
                `;
                
                if (data.reviews.length === 0) {
                    html += '<div class="no-reviews"><span class="material-icons-sharp" style="font-size: 3rem; display: block; margin-bottom: 0.5rem;">rate_review</span>Chưa có đánh giá nào</div>';
                } else {
                    data.reviews.forEach(review => {
                        const date = new Date(review.thoiGian);
                        const formattedDate = date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                        
                        html += `
                            <div class="review-item" id="review-${review.id}">
                                <div class="review-item-header">
                                    <span class="review-item-customer">${review.khachHang}</span>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="review-item-date">${formattedDate}</span>
                                        <button onclick="deleteReview('${review.id}')" style="background: #FEE2E2; color: #B91C1C; border: none; cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;" title="Xóa đánh giá">
                                            <span class="material-icons-sharp" style="font-size: 0.8rem;">delete</span>
                                            Xóa
                                        </button>
                                    </div>
                                </div>
                                <div class="review-item-rating">${generateStars(review.diem)}</div>
                                <div class="review-item-comment">${review.nhanXet || '<em style="color: var(--color-text-gray);">Không có nhận xét</em>'}</div>
                            </div>
                        `;
                    });
                }
                
                modalBody.innerHTML = html;
            })
            .catch(error => {
                modalBody.innerHTML = '<div class="no-reviews" style="color: #B91C1C;"><span class="material-icons-sharp" style="font-size: 3rem; display: block; margin-bottom: 0.5rem;">error</span>Có lỗi xảy ra khi tải đánh giá</div>';
                console.error('Error:', error);
            });
    }
    
    function deleteReview(reviewId) {
        if (!confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
            return;
        }
        
        fetch(`/admin/employees/reviews/${reviewId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload reviews to update the list and average
                loadReviews(currentEmployeeId);
                // Show success message
                alert(data.message);
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa đánh giá');
        });
    }
    
    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.round(rating)) {
                stars += '<span class="material-icons-sharp" style="font-size: 1rem; color: #FFB800;">star</span>';
            } else {
                stars += '<span class="material-icons-sharp" style="font-size: 1rem; color: #D1D5DB;">star</span>';
            }
        }
        return stars;
    }
    
    function closeModal() {
        document.getElementById('reviewsModal').classList.remove('active');
        // Reload page to update average rating in the table
        if (currentEmployeeId) {
            location.reload();
        }
    }
    
    // Close modal when clicking outside
    document.getElementById('reviewsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endsection
