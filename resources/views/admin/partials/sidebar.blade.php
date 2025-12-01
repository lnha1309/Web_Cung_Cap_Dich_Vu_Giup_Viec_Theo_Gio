@php
    $activeItem = $active ?? '';
@endphp

<aside>
    <div class="top">
        <div class="logo" style="display: flex; align-items: center; gap: 0.5rem;">
            <img src="{{ asset('assets/logo.png') }}" class="logo-full" alt="bTaskee Logo" />
        </div>
        <div class="close" id="close-btn">
            <span class="material-icons-sharp"> close </span>
        </div>
    </div>

    <div class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="{{ $activeItem === 'dashboard' ? 'active' : '' }}">
            <span class="material-icons-sharp"> dashboard </span>
            <h3>Dashboard</h3>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="{{ $activeItem === 'orders' ? 'active' : '' }}">
            <span class="material-icons-sharp"> receipt_long </span>
            <h3>Đơn hàng</h3>
        </a>
        <a href="{{ route('admin.services.index') }}" class="{{ $activeItem === 'services' ? 'active' : '' }}">
            <span class="material-icons-sharp"> miscellaneous_services </span>
            <h3>Dịch vụ</h3>
        </a>
        <a href="{{ route('admin.packages.index') }}" class="{{ $activeItem === 'packages' ? 'active' : '' }}">
            <span class="material-icons-sharp"> calendar_month </span>
            <h3>Gói tháng</h3>
        </a>
        <a href="{{ route('admin.surcharges.index') }}" class="{{ $activeItem === 'surcharges' ? 'active' : '' }}">
            <span class="material-icons-sharp"> price_change </span>
            <h3>Phụ thu</h3>
        </a>
        <a href="{{ route('admin.promotions.index') }}" class="{{ $activeItem === 'promotions' ? 'active' : '' }}">
            <span class="material-icons-sharp"> sell </span>
            <h3>Khuyến mãi</h3>
        </a>
        <a href="{{ route('admin.candidates.index') }}" class="{{ $activeItem === 'candidates' ? 'active' : '' }}">
            <span class="material-icons-sharp"> people </span>
            <h3>Ứng viên</h3>
        </a>
        <a href="{{ route('admin.employees.index') }}" class="{{ $activeItem === 'employees' ? 'active' : '' }}">
            <span class="material-icons-sharp"> badge </span>
            <h3>Nhân viên</h3>
        </a>
        <a href="{{ route('admin.customers.index') }}" class="{{ $activeItem === 'customers' ? 'active' : '' }}">
            <span class="material-icons-sharp"> person </span>
            <h3>Khách hàng</h3>
        </a>
        <a href="{{ route('admin.profile.show') }}" class="{{ $activeItem === 'profile' ? 'active' : '' }}">
            <span class="material-icons-sharp"> settings </span>
            <h3>Tài khoản</h3>
        </a>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
            <span class="material-icons-sharp"> logout </span>
            <h3>Logout</h3>
        </a>
        <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</aside>
