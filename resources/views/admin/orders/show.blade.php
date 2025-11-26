@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->ID_DD)

@section('content')
@push('styles')
<style>
    .container {
        grid-template-columns: 14rem auto !important;
    }

    .card {
        background: var(--color-white);
        border-radius: var(--card-border-radius);
        padding: var(--card-padding);
        box-shadow: var(--box-shadow);
        margin-bottom: 2rem;
        transition: all 300ms ease;
    }

    .card:hover {
        box-shadow: none;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--color-light);
        padding-bottom: 1rem;
    }

    .card-header h2 {
        font-size: 1.4rem;
        color: var(--color-dark);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }

    .info-group {
        margin-bottom: 1.5rem;
    }

    .info-label {
        font-weight: 600;
        color: var(--color-dark-variant);
        margin-bottom: 0.5rem;
        display: block;
    }

    .info-value {
        color: var(--color-dark);
        font-size: 1rem;
    }

    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: capitalize;
        display: inline-block;
    }
    .status-badge.warning { background: #fff8e1; color: #ffc107; }
    .status-badge.success { background: #e8f5e9; color: #4caf50; }
    .status-badge.danger { background: #ffebee; color: #f44336; }
    .status-badge.primary { background: #e3f2fd; color: #2196f3; }
    .status-badge.info { background: #e0f7fa; color: #00bcd4; }
    .status-badge.secondary { background: #f3e5f5; color: #9c27b0; }
    .status-badge.dark { background: #eceff1; color: #607d8b; }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--color-dark);
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .btn-back:hover {
        color: var(--color-primary);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    table th, table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--color-light);
    }

    table th {
        font-weight: 600;
        color: var(--color-dark);
    }

    table td {
        color: var(--color-dark-variant);
    }
</style>
@endpush

<div class="container">
    @include('admin.partials.sidebar', ['active' => 'orders'])

    <main>
        <a href="{{ route('admin.orders.index') }}" class="btn-back">
            <span class="material-icons-sharp">arrow_back</span> Quay lại danh sách
        </a>

        <div class="card">
            <div class="card-header">
                <h2>Thông tin đơn hàng #{{ $order->ID_DD }}</h2>
                @php
                    $statusClass = 'primary';
                    $statusLabel = $order->TrangThaiDon;
                    
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
                        case 'confirmed':
                            $statusClass = 'primary';
                            $statusLabel = 'Đã xác nhận';
                            break;
                        case 'done':
                        case 'completed':
                            $statusClass = 'success';
                            $statusLabel = 'Hoàn thành';
                            break;
                        case 'cancelled':
                            $statusClass = 'danger';
                            $statusLabel = 'Đã hủy';
                            break;
                        case 'rejected':
                            $statusClass = 'danger';
                            $statusLabel = 'Đã từ chối';
                            break;
                    }
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>

            <div class="info-grid">
                <div>
                    <div class="info-group">
                        <span class="info-label">Khách hàng</span>
                        <div class="info-value">{{ $order->khachHang->Ten_KH ?? 'N/A' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Số điện thoại</span>
                        <div class="info-value">{{ $order->khachHang->SDT ?? 'N/A' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Địa chỉ làm việc</span>
                        <div class="info-value">{{ $order->diachi->DiaChiDayDu }}</div>
                    </div>
                </div>
                <div>
                    <div class="info-group">
                        <span class="info-label">Dịch vụ</span>
                        <div class="info-value">{{ $order->dichVu->TenDV ?? 'N/A' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Nhân viên thực hiện</span>
                        <div class="info-value">{{ $order->nhanVien->Ten_NV ?? 'Chưa có nhân viên' }}</div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Ngày tạo đơn</span>
                        <div class="info-value">{{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Chi tiết thanh toán</h2>
            </div>
            <div class="info-grid">
                <div>
                    <div class="info-group">
                        <span class="info-label">Tổng tiền</span>
                        <div class="info-value">{{ number_format($order->TongTien) }} đ</div>
                    </div>
                    @if($order->ID_KM)
                    <div class="info-group">
                        <span class="info-label">Mã khuyến mãi</span>
                        <div class="info-value">{{ $order->ID_KM }}</div>
                    </div>
                    @endif
                </div>
                <div>
                    <div class="info-group">
                        <span class="info-label">Tổng tiền sau giảm</span>
                        <div class="info-value" style="font-weight: bold; color: var(--color-primary); font-size: 1.2rem;">
                            {{ number_format($order->TongTienSauGiam) }} đ
                        </div>
                    </div>
                    <div class="info-group">
                        <span class="info-label">Phương thức thanh toán</span>
                        <div class="info-value">{{ $order->PhuongThucThanhToan ?? 'Tiền mặt' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($order->phuThu && $order->phuThu->count() > 0)
        <div class="card">
            <div class="card-header">
                <h2>Phụ thu</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tên phụ thu</th>
                        <th>Giá</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->phuThu as $surcharge)
                    <tr>
                        <td>{{ $surcharge->Ten_PT }}</td>
                        <td>{{ number_format($surcharge->GiaCuoc) }} đ</td>
                        <td>{{ $surcharge->pivot->Ghichu ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($order->LoaiDon == 'month' && $order->lichBuoiThang && $order->lichBuoiThang->count() > 0)
        <div class="card">
            <div class="card-header">
                <h2>Lịch làm việc (Theo tháng)</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Ngày làm</th>
                        <th>Giờ bắt đầu</th>
                        <th>Giờ kết thúc</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->lichBuoiThang as $schedule)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($schedule->NgayLam)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->GioBatDau)->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->GioKetThuc)->format('H:i') }}</td>
                        <td>
                            @if($schedule->TrangThai == 'completed')
                                <span class="status-badge success">Hoàn thành</span>
                            @elseif($schedule->TrangThai == 'cancelled')
                                <span class="status-badge danger">Đã hủy</span>
                            @else
                                <span class="status-badge warning">Chưa làm</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </main>
</div>
@endsection
