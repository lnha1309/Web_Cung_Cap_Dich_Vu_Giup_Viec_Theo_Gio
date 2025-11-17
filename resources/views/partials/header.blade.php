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
        <a href="{{ url('login') }}">
            <button class="btn-login">Đăng nhập</button>
        </a>
        <a href="{{ url('register') }}">
            <button class="btn-signup">Đăng ký</button>
        </a>
    </div>
</header>
