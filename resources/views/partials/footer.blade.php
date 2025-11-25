<footer>
    <div class="footer-container">
        <div class="footer-content">
            <!-- Cột 1: Logo + Thông tin công ty -->
            <div class="footer-section footer-logo-section">
                <img src="{{ asset('assets/logo.png') }}" alt="bTaskee Logo" class="footer-logo">
                <div class="footer-company-info">
                    <p class="company-name">Công Ty TNHH bTaskee</p>
                    <p>Địa chỉ: 284/25/20 Lý Thường Kiệt, Phường Diên Hồng, TP. Hồ Chí Minh 72506</p>
                    <p>Mã số doanh nghiệp: 0313723825</p>
                    <p>Hotline: 1900 636 736</p>
                    <p>Email: support@btaskee.com</p>
                </div>
            </div>

            <!-- Cột 2: CÔNG TY -->
            <div class="footer-section">
                <h3>CÔNG TY</h3>
                <div class="footer-links">
                    <a href="{{ url('introduction') }}">Về chúng tôi</a>
                    <a href="#">Dịch vụ</a>
                    <a href="#">Đội ngũ</a>
                    <a href="{{ url('workerintroduction') }}">Tuyển dụng</a>
                </div>
            </div>

            <!-- Cột 3: THÔNG TIN CHI TIẾT -->
            <div class="footer-section">
                <h3>THÔNG TIN CHI TIẾT</h3>
                <div class="footer-links">
                    <a href="{{ url('contact') }}">Hỗ trợ</a>
                    <a href="#">Chính sách bảo mật</a>
                    <a href="#">Điều khoản và điều kiện</a>
                </div>
            </div>

            <!-- Cột 4: THEO DÕI CHÚNG TÔI -->
            <div class="footer-section">
                <h3>THEO DÕI CHÚNG TÔI</h3>
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="#" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <img src="{{ asset('assets/BoCongThuong.png') }}" alt="Bộ Công Thương" style="width: 100px; height: auto; display: block; margin-left: auto; margin-right: auto; object-fit: contain;">
            <p>© 2016 - 2025 bTaskee Co., Ltd.</p>
        </div>
    </div>
</footer>
