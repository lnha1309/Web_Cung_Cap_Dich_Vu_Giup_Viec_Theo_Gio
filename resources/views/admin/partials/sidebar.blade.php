@php
    $activeItem = $active ?? '';
@endphp

<aside>
    <div class="top">
        <div class="logo" id="logo-toggle" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <img src="{{ asset('assets/logo.png') }}" class="logo-full" alt="bTaskee Logo" />
            <img src="{{ asset('assets/logo2.png') }}" class="logo-collapsed" alt="bTaskee Logo" />
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
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
            <span class="material-icons-sharp"> logout </span>
            <h3>Logout</h3>
        </a>
        <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>
</aside>
