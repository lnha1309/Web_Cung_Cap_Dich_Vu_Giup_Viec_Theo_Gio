@extends('layouts.base')

@section('title', 'Thông báo')

@section('content')
<div class="container" style="margin-top: 100px; margin-bottom: 50px;">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="notification-page">
                <div class="page-header">
                    <h2><i class="fa-solid fa-bell"></i> Thông báo</h2>
                    <div class="filter-buttons">
                        <a href="{{ route('notifications.index') }}" 
                           class="filter-btn {{ $filter === 'all' ? 'active' : '' }}">
                            Tất cả
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                           class="filter-btn {{ $filter === 'unread' ? 'active' : '' }}">
                            Chưa đọc
                        </a>
                    </div>
                </div>

                @if($notifications->isEmpty())
                    <div class="empty-state">
                        <i class="fa-solid fa-bell-slash"></i>
                        <p>{{ $filter === 'unread' ? 'Bạn không có thông báo chưa đọc' : 'Bạn chưa có thông báo nào' }}</p>
                    </div>
                @else
                    <div class="notifications-container">
                        @foreach($notifications as $notification)
                            <div class="notification-card {{ $notification->DaDoc ? '' : 'unread' }}" 
                                 data-id="{{ $notification->ID_TB }}">
                                <div class="notification-icon-wrapper {{ $notification->LoaiThongBao }}">
                                    @switch($notification->LoaiThongBao)
                                        @case('order_created')
                                            <i class="fa-solid fa-cart-plus"></i>
                                            @break
                                        @case('order_cancelled')
                                            <i class="fa-solid fa-times-circle"></i>
                                            @break
                                        @case('order_status_change')
                                            <i class="fa-solid fa-sync"></i>
                                            @break
                                        @case('refund_completed')
                                            <i class="fa-solid fa-money-bill-wave"></i>
                                            @break
                                        @case('finding_staff_delay')
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                            @break
                                        @case('order_rescheduled')
                                            <i class="fa-solid fa-calendar-check"></i>
                                            @break
                                        @default
                                            <i class="fa-solid fa-bell"></i>
                                    @endswitch
                                </div>
                                <div class="notification-body">
                                    <h4>{{ $notification->TieuDe }}</h4>
                                    <p>{{ $notification->NoiDung }}</p>
                                    <span class="notification-datetime">
                                        <i class="fa-solid fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($notification->ThoiGian)->format('H:i d/m/Y') }}
                                    </span>
                                </div>
                                @if(!$notification->DaDoc)
                                    <span class="unread-indicator"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="pagination-wrapper">
                        {{ $notifications->appends(['filter' => $filter])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.notification-page {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.page-header h2 {
    margin: 0;
    color: #333;
    font-size: 24px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.filter-btn {
    padding: 8px 20px;
    border-radius: 20px;
    text-decoration: none;
    color: #666;
    background: #f5f5f5;
    transition: all 0.3s;
}

.filter-btn.active {
    background: #FF6B35;
    color: white;
}

.filter-btn:hover {
    background: #FF6B35;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 60px;
    margin-bottom: 20px;
    color: #ddd;
}

.empty-state p {
    font-size: 16px;
    margin: 0;
}

.notifications-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-card {
    display: flex;
    gap: 15px;
    padding: 20px;
    border-radius: 8px;
    background: #fafafa;
    transition: all 0.3s;
    position: relative;
}

.notification-card.unread {
    background: #fff5f2;
    border-left: 4px solid #FF6B35;
}

.notification-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.notification-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}

.notification-icon-wrapper.order_created {
    background: #e3f2fd;
    color: #2196F3;
}

.notification-icon-wrapper.order_cancelled {
    background: #ffebee;
    color: #f44336;
}

.notification-icon-wrapper.order_status_change {
    background: #e8f5e9;
    color: #4CAF50;
}

.notification-icon-wrapper.refund_completed {
    background: #fff3e0;
    color: #FF9800;
}

.notification-icon-wrapper.finding_staff_delay {
    background: #fff3cd;
    color: #d39e00;
}

.notification-icon-wrapper.order_rescheduled {
    background: #e8f5e9;
    color: #2e7d32;
}

.notification-icon-wrapper.other {
    background: #f5f5f5;
    color: #757575;
}

.notification-body {
    flex: 1;
}

.notification-body h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    color: #333;
}

.notification-body p {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
    line-height: 1.5;
}

.notification-datetime {
    font-size: 12px;
    color: #999;
}

.notification-datetime i {
    margin-right: 4px;
}

.unread-indicator {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 10px;
    height: 10px;
    background: #FF6B35;
    border-radius: 50%;
}

.pagination-wrapper {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .notification-card {
        padding: 15px;
    }
}
</style>
@endsection
