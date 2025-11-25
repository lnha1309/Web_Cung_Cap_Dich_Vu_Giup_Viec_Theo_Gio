<header>
    <div class="logo-container" onclick="window.location.href='{{ url('/') }}'" style="cursor: pointer;">
        <img src="{{ asset('assets/logo.png') }}" alt="bTaskee Logo" class="footer-logo">
    </div>

    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fa-solid fa-bars"></i>
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

    <!-- Off-canvas Menu Overlay -->
    <div class="offcanvas-overlay" id="offcanvasOverlay"></div>

    <!-- Off-canvas Menu -->
    <div class="offcanvas-menu" id="offcanvasMenu">
        <div class="offcanvas-header">
            <div class="offcanvas-logo">
                <img src="{{ asset('assets/logo.png') }}" alt="bTaskee Logo">
            </div>
            <div class="offcanvas-close" id="offcanvasClose">
                <i class="fa-solid fa-times"></i>
            </div>
        </div>
        <div class="offcanvas-content">
            <div class="offcanvas-auth">
                @auth
                    <!-- If logged in, maybe show simple profile link or logout? 
                         For now keeping it simple as per request: Login/Register buttons are for guests.
                         If user is logged in, we might want to show their name or just hide these.
                         The request said: "Nút Đăng nhập (outline) Nút Đăng ký (solid, màu chủ đạo)" in the panel.
                         I will assume this is for guests. If auth, I'll show a "Hello User" or similar.
                    -->
                     <div class="offcanvas-account-menu">
                        <div class="offcanvas-account-toggle" id="offcanvasAccountToggle">
                            <div class="offcanvas-user-info">
                                <div class="user-avatar-circle">{{ $accountInitial ?? 'U' }}</div>
                                <span class="user-name-display">{{ $accountName ?? 'Khách' }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="offcanvas-account-dropdown" id="offcanvasAccountDropdown">
                            <a href="{{ route('bookings.history') }}" class="offcanvas-account-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span>Lịch hẹn</span>
                            </a>
                            <a href="{{ route('profile.show') }}" class="offcanvas-account-item">
                                <i class="fa-solid fa-user"></i>
                                <span>Thông tin cá nhân</span>
                            </a>
                            <a href="{{ url('contact') }}" class="offcanvas-account-item">
                                <i class="fa-solid fa-headset"></i>
                                <span>Hỗ trợ</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="offcanvas-account-form">
                                @csrf
                                <button type="submit" class="offcanvas-account-item offcanvas-account-signout">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span>Đăng xuất</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ url('login') }}" style="width: 100%;">
                        <button class="btn-login" style="width: 100%;">Đăng nhập</button>
                    </a>
                    <a href="{{ url('register') }}" style="width: 100%;">
                        <button class="btn-signup" style="width: 100%;">Đăng ký</button>
                    </a>
                @endauth
            </div>
            <nav class="offcanvas-nav">
                <div class="offcanvas-item-dropdown">
                    <div class="offcanvas-dropdown-toggle">
                        <span>Về bTaskee</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="offcanvas-dropdown-menu">
                        <a href="{{ url('introduction') }}" class="offcanvas-link">Giới thiệu</a>
                        <a href="{{ url('post') }}" class="offcanvas-link">Thông cáo báo chí</a>
                        <a href="{{ url('contact') }}" class="offcanvas-link">Liên hệ</a>
                    </div>
                </div>

                <div class="offcanvas-item-dropdown">
                    <div class="offcanvas-dropdown-toggle">
                        <span>Dịch vụ</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="offcanvas-dropdown-menu">
                        <a href="{{ url('giupviectheogio') }}" class="offcanvas-link">Giúp việc theo giờ</a>
                        <a href="{{ url('giupviectheothang') }}" class="offcanvas-link">Giúp việc theo tháng</a>
                    </div>
                </div>

                <a href="{{ url('workerintroduction') }}" class="offcanvas-link">Trở thành đối tác</a>
            </nav>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const offcanvasMenu = document.getElementById('offcanvasMenu');
            const offcanvasOverlay = document.getElementById('offcanvasOverlay');
            const offcanvasClose = document.getElementById('offcanvasClose');

            function openMenu() {
                offcanvasMenu.classList.add('active');
                offcanvasOverlay.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            }

            function closeMenu() {
                offcanvasMenu.classList.remove('active');
                offcanvasOverlay.classList.remove('active');
                document.body.style.overflow = ''; // Restore scrolling
            }

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', openMenu);
            }

            if (offcanvasClose) {
                offcanvasClose.addEventListener('click', closeMenu);
            }

            if (offcanvasOverlay) {
                offcanvasOverlay.addEventListener('click', closeMenu);
            }

            // Mobile Dropdown Toggle
            const dropdownToggles = document.querySelectorAll('.offcanvas-dropdown-toggle');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    const dropdownMenu = this.nextElementSibling;
                    dropdownMenu.classList.toggle('active');
                    
                    // Optional: Close other dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            otherToggle.classList.remove('active');
                            otherToggle.nextElementSibling.classList.remove('active');
                        }
                    });
                });
            });

            // Offcanvas Account Dropdown Toggle
            const offcanvasAccountToggle = document.getElementById('offcanvasAccountToggle');
            const offcanvasAccountDropdown = document.getElementById('offcanvasAccountDropdown');

            if (offcanvasAccountToggle && offcanvasAccountDropdown) {
                offcanvasAccountToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    offcanvasAccountDropdown.classList.toggle('active');
                });
            }
        });
    </script>
</header>
