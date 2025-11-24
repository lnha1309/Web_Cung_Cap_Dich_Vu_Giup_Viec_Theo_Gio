@extends('layouts.base')

@section('title', 'Chi tiết đơn hàng')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    /* CSS Tùy chỉnh cho đẹp hơn */
    .detail-card { border: none; border-radius: 16px; overflow: hidden; }
    .detail-header { background: linear-gradient(to right, #004D2E, #004D2E); color: white; padding: 20px; }
    .info-label { color: #6c757d; font-size: 0.9rem; margin-bottom: 5px; }
    .info-value { font-weight: 600; color: #212529; font-size: 1.05rem; }
    .price-tag { font-size: 1.5rem; color: #dc3545; font-weight: bold; }
    .btn-back { background: rgba(255, 255, 255, 0.2); color: white; border: none; transition: 0.3s; }
    .btn-back:hover { background: rgba(255, 255, 255, 0.4); color: white; }
</style>

<div class="container py-5" style="background-color: #f4f6f9; min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <div class="card shadow detail-card">
                <div class="detail-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">CHI TIẾT ĐƠN HÀNG</h5>
                        <small class="opacity-75">Mã đơn: #{{ $booking->ID_DD }}</small>
                    </div>
                    <a href="{{ route('bookings.history') }}" class="btn btn-sm btn-back rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
                
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        @if($booking->TrangThaiDon == 'finding_staff')
                            <div class="alert alert-info d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-search me-2"></i>Trạng thái: <strong>Đang tìm nhân viên</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'assigned')
                            <div class="alert alert-primary d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-person-check me-2"></i>Trạng thái: <strong>Đã có nhân viên nhận</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'done')
                            <div class="alert alert-success d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-check-circle-fill me-2"></i>Trạng thái: <strong>Hoàn thành</strong>
                            </div>
                        @elseif($booking->TrangThaiDon == 'cancelled')
                            <div class="alert alert-danger d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                <i class="bi bi-x-circle-fill me-2"></i>Trạng thái: <strong>Đã hủy</strong>
                            </div>
                        @else
                            <div class="alert alert-secondary d-inline-block px-4 py-2 rounded-pill border-0 shadow-sm">
                                Trạng thái: <strong>{{ $booking->TrangThaiDon }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="mb-3">
                                    <p class="info-label"><i class="bi bi-calendar3 me-1"></i>Ngày đặt đơn</p>
                                    <p class="info-value">{{ \Carbon\Carbon::parse($booking->NgayTao)->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="mb-3">
                                    <p class="info-label"><i class="bi bi-clock me-1"></i>Thời gian làm việc</p>
                                    <p class="info-value">
                                        {{ $booking->NgayLam ? \Carbon\Carbon::parse($booking->NgayLam)->format('d/m/Y') : 'Theo gói tháng' }}
                                        <span class="text-muted fw-normal mx-1">|</span>
                                        {{ $booking->GioBatDau ? \Carbon\Carbon::parse($booking->GioBatDau)->format('H:i') : '' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <div class="mb-3">
                                    <p class="info-label"><i class="bi bi-broom me-1"></i>Dịch vụ</p>
                                    <p class="info-value text-primary">
                                        {{ $booking->dichVu->TenDV ?? $booking->ID_DV }}
                                    </p>
                                </div>
                                <div>
                                    <p class="info-label"><i class="bi bi-wallet2 me-1"></i>Tổng thanh toán</p>
                                    <p class="price-tag mb-0">
                                        {{ number_format($booking->TongTienSauGiam) }} đ
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($booking->GhiChu)
                        <div class="col-12">
                            <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded-3">
                                <p class="info-label text-warning-emphasis"><i class="bi bi-journal-text me-1"></i>Ghi chú của bạn:</p>
                                <p class="mb-0 text-dark fst-italic">"{{ $booking->GhiChu }}"</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mt-5 text-center">
                        @if($booking->TrangThaiDon == 'finding_staff')
                            <hr class="my-4 text-muted opacity-25">
                            <p class="text-muted small mb-3">Nếu bạn muốn thay đổi ý định, bạn có thể hủy đơn hàng này.</p>
                            <button class="btn btn-outline-danger px-5 py-2 rounded-pill fw-bold" onclick="alert('Chức năng hủy sẽ được cập nhật sau!')">
                                <i class="bi bi-trash3 me-2"></i>Hủy đơn hàng này
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection