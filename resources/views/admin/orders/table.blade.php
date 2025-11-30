<div id="order-table-container">
    <h2>Danh sách đơn hàng ({{ $currentType == 'hour' ? 'Theo giờ' : 'Theo tháng' }})</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Dịch vụ</th>
                <th>Nhân viên</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            @php
                $statusClass = 'primary';
                $statusLabel = $order->TrangThaiDon;
                
                // Map status to class and label
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
                    case 'done':
                        $statusClass = 'success';
                        $statusLabel = 'Hoàn thành';
                        break;
                    case 'cancelled':
                        $statusClass = 'danger';
                        $statusLabel = 'Đã hủy';
                        break;
                }
            @endphp
            <tr>
                <td>#{{ $order->ID_DD }}</td>
                <td>{{ $order->khachHang->Ten_KH ?? 'N/A' }}</td>
                <td>{{ $order->dichVu->TenDV ?? 'N/A' }}</td>
                <td>{{ $order->nhanVien->Ten_NV ?? 'Chưa có' }}</td>
                <td>
                    <div>{{ number_format($order->TongTienSauGiam) }} đ</div>
                    @php
                        // Lấy số tiền hoàn từ bảng ThongBao
                        $refundNotification = \App\Models\ThongBao::where('ID_KH', $order->ID_KH)
                            ->where('LoaiThongBao', 'refund_completed')
                            ->whereNotNull('DuLieuLienQuan')
                            ->get()
                            ->first(function($notification) use ($order) {
                                $data = $notification->DuLieuLienQuan;
                                return isset($data['ID_DD']) && $data['ID_DD'] === $order->ID_DD;
                            });
                        
                        $refunded = 0;
                        if ($refundNotification && isset($refundNotification->DuLieuLienQuan['refund_amount'])) {
                            $refunded = $refundNotification->DuLieuLienQuan['refund_amount'];
                        }
                    @endphp
                    @if($refunded > 0)
                        <div style="font-size: 0.8rem; color: var(--color-danger); margin-top: 0.2rem;">
                            (Đã hoàn: {{ number_format($refunded) }} đ)
                        </div>
                    @endif
                </td>
                <td>
                    <span class="status-badge {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td>{{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="{{ route('admin.orders.show', $order->ID_DD) }}" class="primary">Chi tiết</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Không có đơn hàng nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-center pagination-container">
        {{ $orders->links('pagination::bootstrap-4') }}
    </div>
</div>
