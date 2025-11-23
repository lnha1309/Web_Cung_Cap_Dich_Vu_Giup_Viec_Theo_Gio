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
    .btn-action { border-radius: 20px; padding: 5px 15px; font-size: 0.85rem; }
</style>

<div class="container py-5" style="background-color: #f4f6f9; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark mb-0">
                    <i class="bi bi-calendar-check text-primary me-2"></i>Quản lý Đơn đặt lịch
                </h2>
                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-house-door me-1"></i>Trang chủ
                </a>
            </div>

            <div class="card shadow-sm mb-5">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold text-primary">
                        <span class="spinner-grow spinner-grow-sm text-primary me-2" role="status"></span>
                        Đơn hàng đang xử lý
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($currentBookings) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Mã đơn</th>
                                    <th>Thời gian</th>
                                    <th>Trạng thái</th>
                                    <th>Tổng tiền</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentBookings as $item)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">#{{ $item->ID_DD }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ $item->NgayLam ? \Carbon\Carbon::parse($item->NgayLam)->format('d/m/Y') : 'Gói tháng' }}</span>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $item->GioBatDau ? \Carbon\Carbon::parse($item->GioBatDau)->format('H:i') : '--:--' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->TrangThaiDon == 'finding_staff')
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info status-badge">
                                                <i class="bi bi-search me-1"></i>Đang tìm NV
                                            </span>
                                        @elseif($item->TrangThaiDon == 'assigned')
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary status-badge">
                                                <i class="bi bi-person-check me-1"></i>Đã có NV
                                            </span>
                                        @elseif($item->TrangThaiDon == 'working')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning status-badge">
                                                <i class="bi bi-play-circle me-1"></i>Đang làm việc
                                            </span>
                                        @else
                                            <span class="badge bg-secondary status-badge">{{ $item->TrangThaiDon }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">
                                        {{ number_format($item->TongTienSauGiam) }} đ
                                    </td>
                                    <td class="text-end pe-4">
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
                        <p class="text-muted fw-medium">Hiện tại bạn không có đơn đặt nào.</p>
                        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-primary">Đặt lịch ngay</a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold text-secondary">
                        <i class="bi bi-clock-history me-2"></i>Lịch sử đơn hàng cũ
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(count($historyBookings) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Mã đơn</th>
                                    <th>Ngày làm</th>
                                    <th>Trạng thái</th>
                                    <th>Tổng tiền</th>
                                    <th class="text-end pe-4">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historyBookings as $item)
                                <tr>
                                    <td class="ps-4 text-muted">#{{ $item->ID_DD }}</td>
                                    <td>{{ $item->NgayLam ? \Carbon\Carbon::parse($item->NgayLam)->format('d/m/Y') : 'Gói tháng' }}</td>
                                    <td>
                                        @if($item->TrangThaiDon == 'done')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success status-badge">
                                                <i class="bi bi-check-circle me-1"></i>Hoàn thành
                                            </span>
                                        @elseif($item->TrangThaiDon == 'cancelled')
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger status-badge">
                                                <i class="bi bi-x-circle me-1"></i>Đã hủy
                                            </span>
                                        @else
                                            <span class="badge bg-secondary status-badge">Kết thúc</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ number_format($item->TongTienSauGiam) }} đ</td>
                                    <td class="text-end pe-4">
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
                        <p class="text-muted mb-0">Chưa có lịch sử đơn hàng nào.</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection