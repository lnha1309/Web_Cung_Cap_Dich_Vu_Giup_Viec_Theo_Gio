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
                        <div class="info-value">
                            {{ $order->nhanVien->Ten_NV ?? 'Chưa có nhân viên' }}
                            @if($order->LoaiDon == 'hour' && $order->TrangThaiDon != 'cancelled' && $order->TrangThaiDon != 'completed' && $order->TrangThaiDon != 'rejected')
                                <button onclick="openAssignOrderModal('{{ $order->ID_DD }}')" title="Đổi nhân viên" style="background: none; border: none; cursor: pointer; color: var(--color-primary); vertical-align: middle; margin-left: 0.5rem;">
                                    <span class="material-icons-sharp" style="font-size: 1.2rem;">edit</span>
                                </button>
                            @endif
                        </div>
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
                        <th>Nhân viên</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->lichBuoiThang as $schedule)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($schedule->NgayLam)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->GioBatDau)->format('H:i') }}</td>
                        <td>
                            @if(isset($schedule->GioKetThuc))
                                {{ \Carbon\Carbon::parse($schedule->GioKetThuc)->format('H:i') }}
                            @else
                                {{ \Carbon\Carbon::parse($schedule->GioBatDau)->addHours($order->ThoiLuongGio ?? 0)->format('H:i') }}
                            @endif
                        </td>
                        <td>
                            @if($schedule->ID_NV)
                                @php
                                    $staff = \App\Models\NhanVien::find($schedule->ID_NV);
                                @endphp
                                {{ $staff->Ten_NV ?? $schedule->ID_NV }}
                                @if($schedule->TrangThaiBuoi != 'cancelled' && $schedule->TrangThaiBuoi != 'completed' && $order->TrangThaiDon != 'cancelled' && $order->TrangThaiDon != 'rejected')
                                    <button onclick="openAssignModal('{{ $schedule->ID_Buoi }}')" title="Đổi nhân viên" style="background: none; border: none; cursor: pointer; color: var(--color-primary); vertical-align: middle;">
                                        <span class="material-icons-sharp" style="font-size: 1.2rem;">edit</span>
                                    </button>
                                @endif
                            @else
                                @if($schedule->TrangThaiBuoi != 'cancelled' && $schedule->TrangThaiBuoi != 'completed' && $order->TrangThaiDon != 'cancelled' && $order->TrangThaiDon != 'rejected')
                                    <button class="status-badge primary" onclick="openAssignModal('{{ $schedule->ID_Buoi }}')" style="border: none; cursor: pointer;">Chọn NV</button>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($schedule->TrangThaiBuoi == 'completed')
                                <span class="status-badge success">Hoàn thành</span>
                            @elseif($schedule->TrangThaiBuoi == 'cancelled')
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

<!-- Assign Staff Modal -->
<div id="assignModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: var(--card-border-radius); width: 500px; max-width: 90%; max-height: 80vh; overflow-y: auto;">
        <h2 style="margin-bottom: 1rem;">Chọn nhân viên</h2>
        <div id="staffList" style="display: flex; flex-direction: column; gap: 1rem;">
            Loading...
        </div>
        <button onclick="closeAssignModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--color-light); border: none; border-radius: 0.5rem; cursor: pointer;">Đóng</button>
    </div>
</div>

<script>
    let currentSessionId = null;
    let currentOrderId = null; // For hourly orders

    function openAssignModal(sessionId) {
        currentSessionId = sessionId;
        currentOrderId = null;
        document.getElementById('assignModal').style.display = 'flex';
        document.getElementById('staffList').innerHTML = 'Đang tải danh sách nhân viên...';

        fetch(`{{ url('admin/orders/staff-available') }}/${sessionId}`)
            .then(response => response.json())
            .then(data => renderStaffList(data))
            .catch(err => handleError(err));
    }

    function openAssignOrderModal(orderId) {
        currentOrderId = orderId;
        currentSessionId = null;
        document.getElementById('assignModal').style.display = 'flex';
        document.getElementById('staffList').innerHTML = 'Đang tải danh sách nhân viên...';

        fetch(`{{ url('admin/orders/staff-available-order') }}/${orderId}`)
            .then(response => response.json())
            .then(data => renderStaffList(data))
            .catch(err => handleError(err));
    }

    function renderStaffList(data) {
        const list = document.getElementById('staffList');
        list.innerHTML = '';
        
        if (data.length === 0) {
            list.innerHTML = '<p>Không tìm thấy nhân viên phù hợp (trống lịch).</p>';
            return;
        }

        data.forEach(staff => {
            const item = document.createElement('div');
            item.style.padding = '1rem';
            item.style.border = '1px solid var(--color-light)';
            item.style.borderRadius = '0.5rem';
            item.style.display = 'flex';
            item.style.justifyContent = 'space-between';
            item.style.alignItems = 'center';
            
            if (staff.is_close) {
                item.style.borderColor = 'var(--color-primary)';
                item.style.background = 'rgba(115, 128, 236, 0.1)';
            }

            const distanceText = staff.distance !== undefined ? `<div style="font-size: 0.85em; color: #666; margin-top: 0.25rem;"><span class="material-icons-sharp" style="font-size: 0.9em; vertical-align: middle;">place</span> ${staff.distance} km</div>` : '';

            item.innerHTML = `
                <div style="flex: 1;">
                    <div style="font-weight: bold;">${staff.name} ${staff.is_close ? '<span style="color: var(--color-primary); font-size: 0.8em;">✓ Gần</span>' : ''}</div>
                    <div style="font-size: 0.9em; color: var(--color-info-dark);">${staff.phone}</div>
                    ${distanceText}
                </div>
                <button onclick="assignStaff('${staff.id}')" style="background: var(--color-primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer;">Chọn</button>
            `;
            list.appendChild(item);
        });
    }

    function handleError(err) {
        console.error(err);
        document.getElementById('staffList').innerHTML = 'Lỗi khi tải danh sách.';
    }

    function closeAssignModal() {
        document.getElementById('assignModal').style.display = 'none';
        currentSessionId = null;
        currentOrderId = null;
    }

    function assignStaff(staffId) {
        if (!currentSessionId && !currentOrderId) return;

        if (!confirm('Xác nhận chọn nhân viên này?')) return;

        let url = '';
        let body = {};

        if (currentSessionId) {
            url = `{{ route('admin.orders.assign-staff') }}`;
            body = { session_id: currentSessionId, staff_id: staffId };
        } else if (currentOrderId) {
            url = `{{ route('admin.orders.assign-staff-order') }}`;
            body = { order_id: currentOrderId, staff_id: staffId };
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(body)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã phân công thành công!');
                location.reload();
            } else {
                alert('Có lỗi xảy ra.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Lỗi kết nối.');
        });
    }
</script>
@endsection
