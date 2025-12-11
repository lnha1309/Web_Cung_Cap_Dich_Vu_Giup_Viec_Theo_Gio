@extends('layouts.base')

@section('title', 'Quản lý đơn hàng')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    .card { border: none; border-radius: 12px; transition: transform 0.2s; }
    .card:hover { transform: translateY(-2px); }
    .status-badge { padding: 8px 12px; border-radius: 30px; font-weight: 500; font-size: 0.85rem; }
    .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
    .btn-action {
        border-radius: 16px;
        padding: 10px 16px;
        font-size: 0.95rem;
        font-weight: 700;
        min-width: 110px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        line-height: 1.2;
    }
    .booking-tabs .nav-link { border-radius: 12px; font-weight: 600; color: #495057; }
    .booking-tabs .nav-link.active { background-color: #0d6efd; color: #fff; box-shadow: 0 8px 22px rgba(13,110,253,0.25); }
    .booking-tab-pane { animation: fadeIn 0.2s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(4px);} to { opacity: 1; transform: translateY(0);} }
    details.booking-accordion { background-color: #fff; border: 1px solid #e9ecef; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.04); }
    details.booking-accordion summary { list-style: none; cursor: pointer; padding: 16px; }
    details.booking-accordion summary::-webkit-details-marker { display: none; }
    details.booking-accordion .chevron { transition: transform 0.2s; }
    details.booking-accordion[open] .chevron { transform: rotate(180deg); }
    details.booking-accordion .session-wrapper { border-top: 1px dashed #e9ecef; padding: 12px 16px 16px; }
    .session-status { font-size: 0.85rem; }
    .staff-chip { display: inline-flex; align-items: center; gap: 8px; }
    .staff-chip img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    .session-meta { font-size: 0.9rem; color: #6c757d; }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .card { border-radius: 10px; }
        .table thead th { font-size: 0.75rem; white-space: nowrap; }
        .table td { font-size: 0.85rem; }
        .btn-action { width: 100%; min-width: auto; padding: 10px 12px; }
        .booking-tabs .nav-link { font-size: 0.9rem; }
        .table-responsive { overflow-x: auto; }
    }

    /* Mobile stacked rows */
    @media (max-width: 576px) {
        .table thead { display: none; }
        .table tr { display: block; border-bottom: 1px solid #e9ecef; margin-bottom: 10px; }
        .table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 10px 12px !important;
        }
        .table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6c757d;
        }
        .table td:last-child { justify-content: flex-end; }
    }
</style>

@php
    $orderStatusMap = [
        'finding_staff' => ['label' => 'Đang tìm NV', 'class' => 'bg-info bg-opacity-10 text-info border border-info', 'icon' => 'bi-search'],
        'assigned'      => ['label' => 'Đã có NV', 'class' => 'bg-primary bg-opacity-10 text-primary border border-primary', 'icon' => 'bi-person-check'],
        'confirmed'     => ['label' => 'Nhân viên xác nhận', 'class' => 'bg-success bg-opacity-10 text-success border border-success', 'icon' => 'bi-check2-circle'],
        'working'       => ['label' => 'Đang làm việc', 'class' => 'bg-warning bg-opacity-10 text-warning border border-warning', 'icon' => 'bi-play-circle'],
        'completed'     => ['label' => 'Hoàn thành', 'class' => 'bg-success bg-opacity-10 text-success border border-success', 'icon' => 'bi-clipboard-check'],
        'rejected'      => ['label' => 'Bị từ chối', 'class' => 'bg-danger bg-opacity-10 text-danger border border-danger', 'icon' => 'bi-exclamation-octagon'],
        'cancelled'     => ['label' => 'Đã hủy', 'class' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary', 'icon' => 'bi-x-circle'],
        'failed'        => ['label' => 'Thanh toán thất bại', 'class' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary', 'icon' => 'bi-exclamation-triangle'],
    ];

    $sessionStatusMap = [
        'finding_staff' => ['label' => 'Đang tìm NV', 'class' => 'bg-info bg-opacity-10 text-info border border-info'],
        'assigned'      => ['label' => 'Đã có NV', 'class' => 'bg-primary bg-opacity-10 text-primary border border-primary'],
        'confirmed'     => ['label' => 'NV đã nhận', 'class' => 'bg-success bg-opacity-10 text-success border border-success'],
        'working'       => ['label' => 'Đang làm việc', 'class' => 'bg-warning bg-opacity-10 text-warning border border-warning'],
        'rejected'      => ['label' => 'NV từ chối', 'class' => 'bg-danger bg-opacity-10 text-danger border border-danger'],
        'completed'     => ['label' => 'Hoàn thành', 'class' => 'bg-success text-white'],
        'cancelled'     => ['label' => 'Đã hủy', 'class' => 'bg-secondary text-white'],
    ];

    $buildAvatar = function ($staff) {
        if (!$staff) {
            return null;
        }

        $avatarRaw = $staff?->HinhAnh;
        if ($avatarRaw && \Illuminate\Support\Str::startsWith($avatarRaw, ['http://', 'https://', '//'])) {
            $avatar = $avatarRaw;
            $host = parse_url($avatarRaw, PHP_URL_HOST);
            $path = parse_url($avatarRaw, PHP_URL_PATH);
            if ($host && in_array($host, ['10.0.2.2', 'localhost', '127.0.0.1'], true) && $path) {
                $avatar = request()->getSchemeAndHttpHost() . $path;
            }
        } elseif ($avatarRaw) {
            $storageUrl = \Illuminate\Support\Facades\Storage::url($avatarRaw);
            $avatar = url($storageUrl);
        } else {
            $avatar = null;
        }

        return $avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($staff->Ten_NV ?? 'NV');
    };
@endphp

<div class="container py-5" style="background-color: #f4f6f9; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark mb-0">
                    <i class="bi bi-calendar-check text-primary me-2"></i>Quản lý đơn đặt lịch
                </h2>
                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-house-door me-1"></i>Trang chủ
                </a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body pb-2">
                    <ul class="nav nav-pills gap-2 booking-tabs" id="bookingTabs">
                        <li class="nav-item">
                            <button class="nav-link active" type="button" data-booking-tab="#hourTab">
                                <i class="bi bi-clock-history me-1"></i>Đơn theo giờ
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" type="button" data-booking-tab="#monthTab">
                                <i class="bi bi-calendar3 me-1"></i>Đơn theo tháng
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div id="hourTab" class="booking-tab-pane">
                <div class="card shadow-sm mb-5">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold text-primary">
                            <span class="spinner-grow spinner-grow-sm text-primary me-2" role="status"></span>
                            Đơn theo giờ đang xử lý
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if(count($hourCurrent) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 90px;">Mã đơn</th>
                                        <th style="width: 220px;">Dịch vụ</th>
                                        <th>Thời gian</th>
                                        <th>Trạng thái</th>
                                        <th>Thanh toán</th>
                                        <th>Nhân viên</th>
                                        <th style="width: 120px;">Tổng tiền</th>
                                        <th class="text-end pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hourCurrent as $item)
                                    @php
                                        $payment = $item->lichSuThanhToan->first();
                                        $method = $payment?->PhuongThucThanhToan;
                                        $methodLabel = match($method) {
                                            'VNPay' => 'VNPay',
                                            'TienMat' => 'Tiền mặt',
                                            default => 'Chưa thanh toán',
                                        };

                                        $badge = $orderStatusMap[$item->TrangThaiDon] ?? ['label' => ucfirst($item->TrangThaiDon), 'class' => 'bg-secondary text-white', 'icon' => null];

                                        $staff = $item->nhanVien;
                                        $avatar = $buildAvatar($staff);
                                    @endphp
                                    <tr>
                                        <td class="ps-4" data-label="Mã đơn">
                                            <span class="fw-bold text-dark">#{{ $item->ID_DD }}</span>
                                        </td>
                                        <td class="fw-semibold text-dark" data-label="Dịch vụ">
                                            {{ $item->dichVu->TenDV ?? 'Chưa cập nhật' }}
                                        </td>
                                        <td data-label="Thời gian">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $item->NgayLam ? \Carbon\Carbon::parse($item->NgayLam)->format('d/m/Y') : 'Gói tháng' }}</span>
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $item->GioBatDau ? \Carbon\Carbon::parse($item->GioBatDau)->format('H:i') : '--:--' }}</small>
                                            </div>
                                        </td>
                                        <td data-label="Trạng thái">
                                            <span class="badge status-badge {{ $badge['class'] }}">
                                                @if(!empty($badge['icon']))<i class="bi {{ $badge['icon'] }} me-1"></i>@endif
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                        <td data-label="Thanh toán"><span class="text-muted">{{ $methodLabel }}</span></td>
                                        <td data-label="Nhân viên">
                                            @if($staff)
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $avatar }}" alt="Nhân viên" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-dark">{{ $staff->Ten_NV }}</span>
                                                        <small class="text-muted">{{ $staff->SDT }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">Đang tìm nhân viên</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-success" data-label="Tổng tiền">
                                            {{ number_format($item->TongTienSauGiam) }} đ
                                        </td>
                                        <td class="text-end pe-4" data-label="Thao tác">
                                            <a href="{{ route('bookings.detail', $item->ID_DD) }}" class="btn btn-primary btn-action shadow-sm">
                                                Chi tiết <i class="bi bi-arrow-right-short"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50" alt="Empty">
                            <p class="text-muted fw-medium">Hiện tại bạn không có đơn theo giờ nào.</p>
                            <a href="{{ url('/') }}" class="btn btn-sm btn-outline-primary">Đặt lịch ngay</a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold text-secondary">
                            <i class="bi bi-clock-history me-2"></i>Lịch sử đơn theo giờ
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if(count($hourHistory) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4" style="width: 90px;">Mã đơn</th>
                                        <th style="width: 220px;">Dịch vụ</th>
                                        <th>Ngày làm</th>
                                        <th>Trạng thái</th>
                                        <th>Thanh toán</th>
                                        <th>Nhân viên</th>
                                        <th style="width: 120px;">Tổng tiền</th>
                                        <th class="text-end pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hourHistory as $item)
                                    @php
                                        $payment = $item->lichSuThanhToan->first();
                                        $method = $payment?->PhuongThucThanhToan;
                                        $methodLabel = match($method) {
                                            'VNPay' => 'VNPay',
                                            'TienMat' => 'Tiền mặt',
                                            default => 'Chưa thanh toán',
                                        };

                                        $badge = $orderStatusMap[$item->TrangThaiDon] ?? ['label' => ucfirst($item->TrangThaiDon), 'class' => 'bg-secondary text-white', 'icon' => null];

                                        $staff = $item->nhanVien;
                                        $avatar = $buildAvatar($staff);
                                    @endphp
                                    <tr>
                                        <td class="ps-4 text-muted" data-label="Mã đơn">#{{ $item->ID_DD }}</td>
                                        <td class="fw-semibold text-dark" data-label="Dịch vụ">
                                            {{ $item->dichVu->TenDV ?? 'Chưa cập nhật' }}
                                        </td>
                                        <td data-label="Ngày làm">{{ $item->NgayLam ? \Carbon\Carbon::parse($item->NgayLam)->format('d/m/Y') : 'Gói tháng' }}</td>
                                        <td data-label="Trạng thái">
                                            <span class="badge status-badge {{ $badge['class'] }}">
                                                @if(!empty($badge['icon']))<i class="bi {{ $badge['icon'] }} me-1"></i>@endif
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                        <td data-label="Thanh toán"><span class="text-muted">{{ $methodLabel }}</span></td>
                                        <td data-label="Nhân viên">
                                            @if($staff)
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $avatar }}" alt="Nhân viên" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold text-dark">{{ $staff->Ten_NV }}</span>
                                                        <small class="text-muted">{{ $staff->SDT }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted small">Không có</span>
                                            @endif
                                        </td>
                                        <td class="text-muted" data-label="Tổng tiền">{{ number_format($item->TongTienSauGiam) }} đ</td>
                                        <td class="text-end pe-4" data-label="Thao tác">
                                            <a href="{{ route('bookings.detail', $item->ID_DD) }}" class="btn btn-outline-secondary btn-action btn-sm">
                                                Xem lại
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Chưa có lịch sử đơn theo giờ.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div id="monthTab" class="booking-tab-pane d-none">
                @php
                    $monthSections = [
                        ['title' => 'Đơn theo tháng đang xử lý', 'items' => $monthCurrent, 'empty' => 'Hiện tại bạn chưa có đơn theo tháng nào.'],
                        ['title' => 'Lịch sử đơn theo tháng', 'items' => $monthHistory, 'empty' => 'Chưa có lịch sử đơn theo tháng.'],
                    ];
                @endphp

                @foreach($monthSections as $section)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold text-secondary">
                            <i class="bi bi-calendar3 me-2"></i>{{ $section['title'] }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($section['items']) > 0)
                            @foreach($section['items'] as $booking)
                                @php
                                    $payment = $booking->lichSuThanhToan->first();
                                    $method = $payment?->PhuongThucThanhToan;
                                    $methodLabel = match($method) {
                                        'VNPay' => 'VNPay',
                                        'TienMat' => 'Tiền mặt',
                                        default => 'Chưa thanh toán',
                                    };

                                    $sessions = $booking->lichBuoiThang->sortBy(function ($session) {
                                        return ($session->NgayLam ?? '') . ' ' . ($session->GioBatDau ?? '');
                                    });
                                    $doneSessions = $sessions->whereIn('TrangThaiBuoi', ['completed', 'cancelled'])->count();
                                    $totalSessions = $sessions->count();
                                    $sessionProgressText = $totalSessions > 0
                                        ? $doneSessions . '/' . $totalSessions . ' đã xong/hủy'
                                        : 'Chưa có buổi';
                                    $isCancelled = $booking->TrangThaiDon === 'cancelled';
                                    $monthBadge = $isCancelled
                                        ? ['label' => 'Đã hủy', 'class' => 'bg-secondary text-white', 'icon' => 'bi-x-circle']
                                        : ['label' => 'Hoạt động', 'class' => 'bg-success bg-opacity-10 text-success border border-success', 'icon' => 'bi-play-circle'];
                                @endphp
                                <details class="booking-accordion mb-3">
                                    <summary class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div class="d-grid w-100" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                                            <div>
                                                <p class="text-muted mb-1 small">Mã gói tháng</p>
                                                <span class="fw-bold text-dark">#{{ $booking->ID_DD }}</span>
                                            </div>
                                            <div>
                                                <p class="text-muted mb-1 small">Dịch vụ</p>
                                                <span class="fw-semibold text-dark">{{ $booking->dichVu->TenDV ?? 'Chưa cập nhật' }}</span>
                                            </div>
                                            <div>
                                                <p class="text-muted mb-1 small">Khung thời gian</p>
                                                <div class="fw-semibold text-dark">
                                                    {{ $booking->NgayBatDauGoi ? \Carbon\Carbon::parse($booking->NgayBatDauGoi)->format('d/m/Y') : '--' }}
                                                    <span class="text-muted mx-1">→</span>
                                                    {{ $booking->NgayKetThucGoi ? \Carbon\Carbon::parse($booking->NgayKetThucGoi)->format('d/m/Y') : '--' }}
                                                </div>
                                                <p class="session-meta mb-0">Tổng {{ $totalSessions }} buổi{{ $totalSessions ? ' • ' . $sessionProgressText : '' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-muted mb-1 small">Thanh toán</p>
                                                <span class="text-muted">{{ $methodLabel }}</span>
                                            </div>
                                            <div>
                                                <p class="text-muted mb-1 small">Tổng tiền</p>
                                                <span class="fw-bold text-success">{{ number_format($booking->TongTienSauGiam) }} đ</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="mb-2">
                                                <span class="badge status-badge {{ $monthBadge['class'] }}">
                                                    @if(!empty($monthBadge['icon']))<i class="bi {{ $monthBadge['icon'] }} me-1"></i>@endif
                                                    {{ $monthBadge['label'] }}
                                                </span>
                                            </div>
                                            <span class="text-primary fw-semibold d-inline-flex align-items-center gap-1">
                                                Xem buổi
                                                <i class="bi bi-chevron-down chevron"></i>
                                            </span>
                                        </div>
                                    </summary>
                                    <div class="session-wrapper">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                                            <div class="text-muted small">Các buổi trong gói được quản lý riêng: trạng thái và nhân viên áp dụng cho từng buổi.</div>
                                            <a href="{{ route('bookings.detail', $booking->ID_DD) }}" class="btn btn-outline-primary btn-sm">
                                                Chi tiết gói
                                            </a>
                                        </div>

                                        @if($sessions->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Ngày làm</th>
                                                        <th>Giờ</th>
                                                        <th>Nhân viên</th>
                                                        <th>Trạng thái buổi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($sessions as $index => $session)
                                                        @php
                                                            $startTime = $session->GioBatDau ? \Carbon\Carbon::parse($session->GioBatDau) : null;
                                                            $duration = $booking->ThoiLuongGio ?? $booking->dichVu->ThoiLuong ?? 2;
                                                            $endTime = $startTime ? $startTime->copy()->addHours($duration) : null;
                                                            $sessionBadge = $sessionStatusMap[$session->TrangThaiBuoi] ?? ['label' => $session->TrangThaiBuoi ?? 'Đang xử lý', 'class' => 'bg-secondary text-white'];
                                                            $sessionStaff = $session->nhanVien;
                                                            $sessionAvatar = $buildAvatar($sessionStaff);
                                                            $sessionDate = $session->NgayLam ? \Carbon\Carbon::parse($session->NgayLam)->format('d/m/Y') : '--/--/----';
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>{{ $sessionDate }}</td>
                                                            <td>
                                                                <div class="fw-semibold">
                                                                    {{ $startTime ? $startTime->format('H:i') : '--:--' }}
                                                                    -
                                                                    {{ $endTime ? $endTime->format('H:i') : '--:--' }}
                                                                </div>
                                                                <small class="text-muted">({{ $duration }} giờ)</small>
                                                            </td>
                                                            <td>
                                                                @if($sessionStaff)
                                                                    <div class="staff-chip">
                                                                        <img src="{{ $sessionAvatar }}" alt="Nhân viên" />
                                                                        <div class="d-flex flex-column">
                                                                            <span class="fw-semibold text-dark">{{ $sessionStaff->Ten_NV }}</span>
                                                                            <small class="text-muted">{{ $sessionStaff->SDT }}</small>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <span class="text-muted small">Đang tìm nhân viên</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge session-status {{ $sessionBadge['class'] }}">
                                                                    {{ $sessionBadge['label'] }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @else
                                        <div class="text-center py-3 text-muted small">Gói này chưa có buổi nào được lên lịch.</div>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">{{ $section['empty'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabButtons = document.querySelectorAll('[data-booking-tab]');
        const tabPanes = document.querySelectorAll('.booking-tab-pane');

        tabButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-booking-tab');
                tabButtons.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.add('d-none'));

                btn.classList.add('active');
                const pane = document.querySelector(target);
                if (pane) {
                    pane.classList.remove('d-none');
                }
            });
        });
    });
</script>
@endsection
