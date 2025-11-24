<header>
    <div class="logo-container" onclick="window.location.href='{{ url('/') }}'" style="cursor: pointer;">
        <img src="{{ asset('assets/logo.png') }}" alt="bTaskee Logo" class="footer-logo">
    </div>

    <nav class="nav-menu">
        <div class="nav-item-dropdown">
            <span class="nav-link">Về bTaskee</span>
            <div class="dropdown-menu">
                <a href="{{ url('introduction') }}" class="dropdown-link">Giới thiệu</a>
                <a href="{{ url('post') }}" class="dropdown-link">Thông cáo báo chí</a>
                <a href="{{ url('contact') }}" class="dropdown-link">Liên hệ</a>
            </div>
        </div>

        <div class="nav-item-dropdown">
            <span class="nav-link">Dịch vụ</span>
            <div class="dropdown-menu">
                <a href="{{ url('giupviectheogio') }}" class="dropdown-link">Giúp việc theo giờ</a>
                <a href="{{ url('giupviectheothang') }}" class="dropdown-link">Giúp việc theo tháng</a>
            </div>
        </div>

        <a href="{{ url('workerintroduction') }}" class="nav-link">Trở thành đối tác</a>
    </nav>

    <div class="auth-buttons">
        @auth
            @php
                $currentUser = Auth::user();
                $accountName = $currentUser->display_name ?? $currentUser->TenDN ?? 'My Account';
                $accountInitial = mb_strtoupper(mb_substr($accountName, 0, 1, 'UTF-8'), 'UTF-8');
            @endphp
            <div class="account-menu">
                <button type="button" class="account-button" id="accountMenuToggle">
                    <span class="account-name">{{ $accountName }}</span>
                    <span class="account-avatar">{{ $accountInitial }}</span>
                    <span class="account-arrow">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>
                </button>
                <div class="account-dropdown" id="accountMenuDropdown">
                    <a href="{{ url('/') }}" class="account-dropdown-item">
                        <i class="fa-solid fa-house"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="{{ route('bookings.history') }}" class="account-dropdown-item">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Lịch hẹn</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="account-dropdown-item">
                        <i class="fa-solid fa-user"></i>
                        <span>Thông tin cá nhân</span>
                    </a>
                    <a href="{{ url('contact') }}" class="account-dropdown-item">
                        <i class="fa-solid fa-headset"></i>
                        <span>Hỗ trợ</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="account-dropdown-form">
                        @csrf
                        <button type="submit" class="account-dropdown-item account-signout">
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <a href="{{ url('login') }}">
                <button class="btn-login">Đăng nhập</button>
            </a>
            <a href="{{ url('register') }}">
                <button class="btn-signup">Đăng ký</button>
            </a>
        @endauth
    </div>
</header>
