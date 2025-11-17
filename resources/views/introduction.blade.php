@extends('layouts.base')
@section('title', 'Giới thiệu')
@section('global_styles')
<link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
@endsection
@push('styles')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
                sans-serif;
        overflow-x: hidden;
    }

    /* Mission Section */
    .mission-section {
        position: relative;
        width: 100%;
        padding: 60px 20px;
        background: white;
        text-align: center;
    }

    .mission-label {
        font-size: 0.75rem;
        letter-spacing: 2.5px;
        color: #666;
        margin-bottom: 15px;
        font-weight: 500;
    }

    .mission-title {
        font-size: 2.2rem;
        line-height: 1.3;
        color: #2d2d2d;
        font-weight: 700;
        max-width: 900px;
        margin: 0 auto 40px;
    }

    .mission-section p {
        font-size: 1rem;
        line-height: 1.8;
        color: #555;
        max-width: 900px;
        margin: 0 auto 40px;
        text-align: justify;
        white-space: pre-line;
        padding: 0 20px;
    }

    .banner-image {
        width: 90%;
        max-width: 1000px;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    /* Video Section */
    .video-section {
        position: relative;
        width: 100%;
        height: 100vh;
        overflow: hidden;
    }

    .intro-video {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        object-fit: cover;
        pointer-events: none;
    }

    /* About Section */
    .about-section {
        padding: 80px 20px;
        background: #ffffff;
    }

    .about-item {
        max-width: 1200px;
        margin: 0 auto 100px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        align-items: center;
    }

    .about-item:last-child {
        margin-bottom: 0;
    }

    /* Reverse layout cho item chẵn */
    .about-item:nth-child(even) .about-image {
        order: 2;
    }

    .about-item:nth-child(even) .about-content {
        order: 1;
    }

    .about-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 20px;
    }

    .about-content {
        padding: 20px;
    }

    .about-title {
        font-size: 2rem;
        line-height: 1.3;
        color: #2d2d2d;
        font-weight: 700;
        margin-bottom: 25px;
    }

    .about-description {
        font-size: 1rem;
        line-height: 1.7;
        color: #555;
    }

    /* Values Section */
    .value-section {
        padding: 80px 20px;
        background: #ebe3d7;
    }

    .value-title {
        font-size: 2rem;
        text-align: center;
        color: #2d2d2d;
        font-weight: 700;
        margin-bottom: 50px;
    }

    .value-container {
        max-width: 1400px;
        margin: 0 auto 80px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .value-card {
        border-radius: 20px;
        padding: 35px 25px;
        text-align: center;
        color: white;
        transition: transform 0.3s ease;
    }

    .value-card:hover {
        transform: translateY(-5px);
    }

    .value-card.quality {
        background: #FF80DF;
    }

    .value-card.convenience {
        background: #FF6712;
    }

    .value-card.dedication {
        background: #007749;
    }

    .value-card.trust {
        background: #3B82F6;
    }

    .value-icon {
        width: 40px;
        height: 40px;
        margin: 0 auto 20px;
        display: block;
    }

    .value-card-title {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .value-card-description {
        font-size: 0.9rem;
        line-height: 1.5;
        opacity: 0.95;
        font-weight: 500;
    }

    /* Vision Mission Section */
    .vision-mission-container {
        max-width: 1200px;
        margin: 0 auto;
        background: #f5f5f0;
        border-radius: 30px;
        padding: 30px 20px;
    }

    .vision-mission-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
    }

    .vision-block,
    .mission-block {
        padding: 20px;
    }

    .block-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
    }

    .block-icon {
        width: 60px;
        height: 60px;
        margin-right: 20px;
    }

    .block-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .block-title {
        font-size: 1.8rem;
        color: #2d2d2d;
        font-weight: 700;
    }

    .block-description {
        font-size: 1rem;
        line-height: 1.8;
        color: #555;
    }

    /* Partners Section */
    .partners-section {
        padding: 80px 20px;
        background: white;
    }

    .partners-title {
        font-size: 2rem;
        text-align: left;
        color: #2d2d2d;
        font-weight: 700;
        margin-bottom: 50px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .partners-container {
        max-width: 1200px;
        margin: 0 auto 50px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .partner-card {
        background: white;
        border-radius: 15px;
        padding: 40px 30px;
        display: flex;
        align-items: center;
        gap: 25px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .partner-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .partner-logo {
        width: 100px;
        height: 100px;
        flex-shrink: 0;
    }

    .partner-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .partner-info {
        flex: 1;
    }

    .partner-name {
        font-size: 1.4rem;
        color: #2d2d2d;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .partner-description {
        font-size: 0.95rem;
        line-height: 1.6;
        color: #555;
    }

    /* Back to Home Button */
    .back-home-container {
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
    }

    .back-home-btn {
        display: inline-block;
        background: #004d2e;
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        padding: 14px 30px;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 77, 46, 0.2);
    }

    .back-home-btn:hover {
        background: #003d24;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 77, 46, 0.3);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .value-container {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 992px) {
        .value-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .value-title {
            font-size: 2rem;
            margin-bottom: 40px;
        }

        .vision-mission-grid {
            grid-template-columns: 1fr;
            gap: 50px;
        }

        .vision-mission-container {
            padding: 50px 30px;
        }

        .partners-container {
            grid-template-columns: 1fr;
            gap: 25px;
        }
    }

    @media (max-width: 768px) {
        .mission-section {
            padding: 50px 15px;
        }

        .mission-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .mission-section p {
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 30px;
            padding: 0 15px;
        }

        .banner-image {
            width: 95%;
        }

        .about-section {
            padding: 60px 15px;
        }

        .about-item {
            grid-template-columns: 1fr;
            gap: 30px;
            margin-bottom: 60px;
        }

        .about-item:nth-child(even) .about-image,
        .about-item:nth-child(even) .about-content {
            order: initial;
        }

        .about-image {
            height: 350px;
        }

        .about-title {
            font-size: 1.5rem;
        }

        .about-content {
            padding: 0;
        }

        .value-section {
            padding: 60px 15px;
        }

        .value-card {
            padding: 30px 25px;
        }

        .vision-mission-container {
            padding: 40px 25px;
            border-radius: 20px;
        }

        .block-title {
            font-size: 1.5rem;
        }

        .block-icon {
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }

        .partners-section {
            padding: 60px 15px;
        }

        .partners-title {
            font-size: 1.6rem;
            margin-bottom: 35px;
        }

        .partner-card {
            padding: 30px 25px;
            flex-direction: column;
            text-align: center;
        }

        .partner-logo {
            width: 80px;
            height: 80px;
        }

        .back-home-btn {
            font-size: 1rem;
            padding: 16px 40px;
        }
    }

    @media (max-width: 480px) {
        .mission-title {
            font-size: 1.3rem;
        }

        .mission-section p {
            font-size: 0.9rem;
            line-height: 1.6;
            text-align: left;
        }

        .about-title {
            font-size: 1.3rem;
        }

        .about-image {
            height: 300px;
        }

        .value-title {
            font-size: 1.5rem;
        }

        .value-icon {
            width: 50px;
            height: 50px;
            margin-bottom: 15px;
        }

        .value-card-title {
            font-size: 1rem;
        }

        .value-card-description {
            font-size: 0.85rem;
        }

        .vision-mission-container {
            padding: 35px 20px;
        }

        .block-title {
            font-size: 1.3rem;
        }

        .block-description {
            font-size: 0.9rem;
        }

        .block-header {
            margin-bottom: 20px;
        }

        .block-icon {
            width: 45px;
            height: 45px;
        }

        .partners-title {
            font-size: 1.4rem;
        }

        .partner-card {
            padding: 25px 20px;
        }

        .partner-name {
            font-size: 1.2rem;
        }

        .partner-description {
            font-size: 0.85rem;
        }

        .back-home-btn {
            font-size: 0.95rem;
            padding: 14px 35px;
        }
    }
</style>
@endpush
@section('content')
<!-- Mission Section -->
    <section class="mission-section">
        <div class="mission-label">CHÀO BẠN</div>
        <h1 class="mission-title">
            CHÚNG TÔI LÀ BTASKEE
        </h1>
        <p>Công ty TNHH bTaskee được thành lập vào ngày 30 tháng 03 năm 2016 bởi CEO – Founder Nathan Do (Đỗ Đắc Nhân Tâm).

bTaskee là doanh nghiệp tiên phong trong việc ứng dụng công nghệ vào ngành giúp việc nhà ở Việt Nam. Chúng tôi cung cấp đa dịch vụ tiện ích như: dọn dẹp nhà, vệ sinh máy lạnh, đi chợ, … tại Đông Nam Á. Thông qua ứng dụng đặt lịch dành cho khách hàng bTaskee và ứng dụng nhận việc của cộng tác viên bTaskee Partner, khách hàng và cộng tác viên có thể chủ động đăng và nhận việc trực tiếp trên ứng dụng.</p>
        
        <img src="assets/banner.svg" alt="Decorative banner" class="banner-image">
    </section>

    <!-- Video Section -->
    <section class="video-section">
        <video class="intro-video" autoplay muted loop playsinline>
            <source src="assets/introduction-video.mp4" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ video.
        </video>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <!-- Item 1: Hình bên trái, text bên phải -->
        <div class="about-item">
            <img src="assets/hinhyNghia.png" alt="Who we are" class="about-image">
            <div class="about-content">
                <h2 class="about-title">Ý nghĩa của bTaskee</h2>
                <p class="about-description">
                    Tên gọi bTaskee lấy cảm hứng từ hình ảnh những chú ong chăm chỉ (bee) để nói về các chị cộng tác viên giúp việc luôn hoàn thành tốt công việc (Task) được giao. Họ (bTasker) - những chú ong siêng năng, chăm chỉ và cần mẫn - sẽ cung cấp cho khách hàng những dịch vụ chất lượng cao một cách tiện lợi và nhanh chóng.
                </p>
            </div>
        </div>

        <!-- Item 2: Text bên trái, hình bên phải -->
        <div class="about-item">
            <img src="assets/hinhKhuVucHD.png" alt="Our story" class="about-image">
            <div class="about-content">
                <h2 class="about-title">Khu vực hoạt động</h2>
                <p class="about-description">
                    Hiện tại, bTaskee cung cấp các dịch vụ tiện ích cho nhiều hộ gia đình ở khắp hơn 20 tỉnh thành phố lớn tại Việt Nam: Hà Nội, Hải Phòng, Đà Nẵng, Hội An, Nha Trang, Đà Lạt, Bình Dương, Biên Hòa, TP.HCM, Cần Thơ và hơn 10 tỉnh thành khác. Ngoài ra, bTaskee đang mở rộng ra thị trường nước ngoài với dịch vụ chính là giúp việc nhà theo giờ tại Thái Lan và Indonesia.
                </p>
            </div>
        </div>

        <!-- Item 3: Hình bên trái, text bên phải -->
        <div class="about-item">
            <img src="assets/hinhPTNH.png" alt="Our partners" class="about-image">
            <div class="about-content">
                <h2 class="about-title">Phát triển nhiều hơn nữa</h2>
                <p class="about-description">
                    Tại Việt Nam, tính đến nay, bTaskee đã giúp hơn 10,000 người giúp việc có thu nhập ổn định và đáp ứng nhu cầu chăm sóc nhà cửa cho hơn 1,000,000 khách hàng. Với mục tiêu mang đến cho khách hàng những trải nghiệm dịch vụ tốt nhất, bTaskee không ngừng cải thiện chất lượng dịch vụ, ứng dụng.
                </p>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="value-section">
        <h2 class="value-title">Giá trị cốt lõi.</h2>
        
        <div class="value-container">
            <!-- Card 1: Quality -->
            <div class="value-card quality">
                <img src="assets/iconChatLuong.png" alt="Quality icon" class="value-icon">
                <h3 class="value-card-title">Chất lượng</h3>
                <p class="value-card-description">
                    Đảm bảo chất lượng dịch vụ cho khách hàng. Tất cả các bTasker của chúng tôi đều phải có kinh nghiệm và trải qua chương trình đào tạo, kiểm tra, thử việc nghiêm ngặt.
                </p>
            </div>

            <!-- Card 2: Convenience -->
            <div class="value-card convenience">
                <img src="assets/iconTienLoi.png" alt="Convenience icon" class="value-icon">
                <h3 class="value-card-title">Tiện lợi</h3>
                <p class="value-card-description">
                    Ứng dụng nhanh chóng tiện lợi để đặt và nhận việc mọi lúc mọi nơi. Chỉ mất 60 giây để đăng việc, giờ làm việc vô cùng linh hoạt.
                </p>
            </div>

            <!-- Card 3: Dedication -->
            <div class="value-card dedication">
                <img src="assets/iconTanTam.png" alt="Dedication icon" class="value-icon">
                <h3 class="value-card-title">Tận tâm</h3>
                <p class="value-card-description">
                    Luôn đặt mình vào vị trí của khách hàng để mang lại hiệu quả công việc tốt nhất.
                </p>
            </div>

            <!-- Card 4: Trust -->
            <div class="value-card trust">
                <img src="assets/iconCaiTien.png" alt="Trust icon" class="value-icon">
                <h3 class="value-card-title">Cải tiến</h3>
                <p class="value-card-description">
                    Chúng tôi luôn lắng nghe để không ngừng cải tiến công nghệ, đổi mới chính sách, vì trải nghiệm trọn vẹn của người dùng là đích đến của chúng tôi.
                </p>
            </div>
        </div>

        <!-- Vision & Mission Section -->
        <div class="vision-mission-container">
            <div class="vision-mission-grid">
                <!-- Vision Block -->
                <div class="vision-block">
                    <div class="block-header">
                        <div class="block-icon">
                            <img src="assets/hinhTamNhin.png" alt="Vision icon">
                        </div>
                        <h2 class="block-title">Tầm nhìn</h2>
                    </div>
                    <p class="block-description">
                        Không chỉ muốn giúp bạn chăm sóc gia đình từ những dịch vụ dọn dẹp nhà, vệ sinh máy lạnh, nấu ăn gia đình, giặt ủi,... bTaskee đang nỗ lực trở thành công ty hàng đầu Việt Nam và vươn ra thị trường Đông Nam Á, cung cấp nhiều hơn những dịch vụ tiện ích gia đình tích hợp trên ứng dụng di động.
                    </p>
                </div>

                <!-- Mission Block -->
                <div class="mission-block">
                    <div class="block-header">
                        <div class="block-icon">
                            <img src="assets/hinhSuMenh.png" alt="Mission icon">
                        </div>
                        <h2 class="block-title">Sứ mệnh</h2>
                    </div>
                    <p class="block-description">
                        bTaskee ra đời với sứ mệnh đáp ứng nhu cầu giải quyết việc nhà của người dân đô thị và nâng cao giá trị nghề giúp việc nhà bằng cách xây dựng nguồn nhân lực giúp việc bài bản, chuyên nghiệp và tận tâm. Cuộc sống thành thới khi nhe đi gánh nặng việc nhà của khách hàng và nguồn thu nhập ổn định của người lao động chính là những gì mà chúng tôi luôn hướng đến.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="partners-section">
        <h2 class="partners-title">Đối tác của bTaskee</h2>
        
        <div class="partners-container">
            <!-- Partner 1: ZaloPay -->
            <div class="partner-card">
                <div class="partner-logo">
                    <img src="assets/iconZaloPay.png" alt="ZaloPay logo">
                </div>
                <div class="partner-info">
                    <h3 class="partner-name">ZALOPAY</h3>
                    <p class="partner-description">Ứng dụng thanh toán di động nhanh trong 2 giây.</p>
                </div>
            </div>

            <!-- Partner 2: MoMo -->
            <div class="partner-card">
                <div class="partner-logo">
                    <img src="assets/iconMoMo.png" alt="MoMo logo">
                </div>
                <div class="partner-info">
                    <h3 class="partner-name">MOMO</h3>
                    <p class="partner-description">Siêu ứng dụng thanh toán số 1 Việt Nam</p>
                </div>
            </div>

            <!-- Partner 3: Hoozing -->
            <div class="partner-card">
                <div class="partner-logo">
                    <img src="assets/iconHoozing.png" alt="Hoozing logo">
                </div>
                <div class="partner-info">
                    <h3 class="partner-name">HOOZING</h3>
                    <p class="partner-description">Ứng dụng mua và thuê nhà</p>
                </div>
            </div>
        </div>

        <!-- Back to Home Button -->
        <div class="back-home-container">
            <a href="{{ url('/') }}" class="back-home-btn">Quay về trang chủ</a>
        </div>
    </section>
@endsection