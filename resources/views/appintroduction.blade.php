@extends('layouts.base')
@section('title', 'App Introduction')
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .app-intro-container {
            width: 100%;
            background-color: #ebe3d7;
            padding: 10px 40px;
            position: relative;
            overflow: hidden;
        }

        .app-intro-section {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            align-items: center;
            gap: 30px;
            position: relative;
        }

        .app-left-side {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .app-phone-wrapper {
            position: relative;
        }

        .app-phone-wrapper img {
            width: 180px;
            height: auto;
            border-radius: 20px;
        }

        .app-sparkle {
            position: absolute;
            font-size: 40px;
            color: #333;
        }

        .sparkle-top {
            top: -30px;
            right: 40px;
        }

        .sparkle-left {
            bottom: 40px;
            left: -40px;
        }

        .app-shield-wrapper {
            position: relative;
        }

        .app-shield-wrapper img {
            width: 140px;
            height: auto;
        }

        .app-center-content {
            text-align: center;
            padding: 20px;
        }

        .app-brand {
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 2px;
            color: #333;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .app-title {
            font-size: 40px;
            font-weight: 700;
            color: #2d2d2d;
            line-height: 1.2;
            margin-bottom: 30px;
        }

        .app-description {
            font-size: 18px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .app-cta-button {
            background-color: #004d2e;
            color: white;
            border: none;
            padding: 16px 40px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 77, 46, 0.3);
            display: inline-block;
            text-decoration: none;
        }

        .app-cta-button:hover {
            background-color: #003d24;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 77, 46, 0.4);
        }

        .app-cta-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 77, 46, 0.3);
        }

        .app-right-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .app-avatar-wrapper {
            position: relative;
        }

        .app-avatar-wrapper img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
        }

        .app-price-badge {
            background: linear-gradient(135deg, #e879f9 0%, #d946ef 100%);
            color: white;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .price-label {
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .price-amount {
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 5px;
        }

        .price-period {
            font-size: 16px;
            font-weight: 400;
        }

        .sparkle-bottom {
            position: absolute;
            bottom: -40px;
            left: 50%;
            font-size: 50px;
            color: #333;
        }

        /* Features Section */
        .features-container {
            width: 100%;
            background-color: #f5f5f5;
            padding: 60px 40px;
        }

        .features-title {
            text-align: center;
            font-size: 35px;
            font-weight: 700;
            color: #2d2d2d;
            margin-bottom: 50px;
        }

        .features-section {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 60px;
            align-items: start;
        }

        .feature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .feature-icon {
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
        }

        .feature-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .feature-description {
            font-size: 18px;
            line-height: 1.6;
            color: #333;
            max-width: 280px;
        }

        .feature-description strong {
            font-weight: 700;
            color: #2d2d2d;
            display: block;
            margin-bottom: 8px;
        }

        /* Target Audience Section */
.audience-container {
    width: 100%;
    background-color: #f5f5f5;
    padding: 60px 40px;
}

.audience-wrapper {
    max-width: 1240px;
    margin: 0 auto;
    background-color: #ebe3d7;
    border-radius: 30px;
    padding: 20px 0px;
}

.audience-title {
    text-align: center;
    font-size: 35px;
    font-weight: 700;
    color: #2d2d2d;
    margin-bottom: 40px;
}

.audience-card {
    max-width: 1100px;
    margin: 0 auto;
    background-color: white;
    border-radius: 20px;
    padding: 20px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}


        .audience-icon-left,
        .audience-icon-right {
            flex-shrink: 0;
        }

        .audience-icon-left img {
            width: 100px;
            height: auto;
        }

        .audience-icon-right img {
            width: 120px;
            height: auto;
        }

        .audience-content {
            flex: 1;
        }

        .audience-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .audience-list li {
            font-size: 17px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 15px;
            padding-left: 30px;
            position: relative;
            text-align: left;
        }

        .audience-list li:last-child {
            margin-bottom: 0;
        }

        .audience-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #004d2e;
            font-weight: 700;
            font-size: 20px;
        }

        @media (max-width: 1024px) {
    .audience-wrapper {
        padding: 50px 30px;
        border-radius: 25px;
    }

    .audience-card {
        padding: 40px 50px;
        gap: 30px;
    }

    .audience-icon-left img {
        width: 80px;
    }

    .audience-icon-right img {
        width: 100px;
    }

    .audience-title {
        font-size: 36px;
    }
}

@media (max-width: 768px) {
    .audience-container {
        padding: 40px 20px;
    }

    .audience-wrapper {
        border-radius: 20px;
        padding: 40px 20px;
    }

    .audience-card {
        flex-direction: column;
        padding: 40px 30px;
        gap: 25px;
    }

    .audience-icon-left,
    .audience-icon-right {
        display: none;
    }

    .audience-list li {
        font-size: 15px;
        padding-left: 25px;
    }

    .audience-title {
        font-size: 28px;
        margin-bottom: 30px;
    }
}
/* How It Works Section */
.howwork-container {
    width: 100%;
    background-color: #f5f5f5;
    padding: 10px 30px;
}

.howwork-title {
    text-align: left;
    font-size: 35px;
    font-weight: 700;
    color: #2d2d2d;
    margin-bottom: 50px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 80px;  /* Dịch qua phải - thay đổi giá trị này */
}



.howwork-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 60px;
    align-items: center;
}

.howwork-steps {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px 20px;
    margin-left: 40px;  /* Thêm dòng này để dịch qua phải */
}

.step-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.step-number {
    flex-shrink: 0;
    width: 45px;
    height: 45px;
    background-color: #2d2d2d;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
}

.step-text {
    flex: 1;
    font-size: 16px;
    line-height: 1.5;
    color: #333;
    padding-top: 8px;
}

.step-link {
    text-decoration: underline;
    color: #333;
    cursor: pointer;
}

.howwork-image {
    display: flex;
    justify-content: center;
    align-items: center;
}

.howwork-image img {
    max-width: 60%;
    height: auto;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

@media (max-width: 1024px) {
    .howwork-content {
        gap: 40px;
    }

    .howwork-title {
        font-size: 36px;
    }

    .step-text {
        font-size: 15px;
    }

    .howwork-steps {
        gap: 12px 15px;
    }
}

@media (max-width: 768px) {
    .howwork-container {
        padding: 40px 20px;
    }

    .howwork-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }

    .howwork-title {
        font-size: 32px;
        margin-bottom: 40px;
        text-align: center;  /* Căn giữa lại trên mobile */
    }

    .howwork-steps {
        grid-template-columns: 1fr;
        gap: 15px;
        margin-left: 0;  /* Bỏ margin trên mobile */
        padding-left: 0;  /* Bỏ padding trên mobile */
    }

    .step-number {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .step-text {
        font-size: 15px;
    }

    .howwork-image {
        order: -1;
    }
}

/* FAQ Section */
.faq-container {
    width: 100%;
    background-color: #ebe3d7;
    padding: 60px 40px;
    text-align: center;
}

.faq-title {
    font-size: 35px;
    font-weight: 700;
    color: #2d2d2d;
    margin-bottom: 50px;
}

.faq-title-italic {
    font-style: italic;
    font-weight: 400;
}

.faq-cards {
    max-width: 1200px;
    margin: 0 auto 40px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}

.faq-card {
    border-radius: 25px;
    padding: 30px 25px;  /* Giảm padding từ 40px 35px xuống 30px 25px */
    text-align: center;  /* Căn giữa thay vì left */
    min-height: 160px;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
}

.faq-card:hover {
    transform: translateY(-5px);
}

.faq-card-yellow {
    background: linear-gradient(135deg, #f4d03f 0%, #f9c74f 100%);
}

.faq-card-cyan {
    background: linear-gradient(135deg, #7dd3c0 0%, #5fc9b8 100%);
}

.faq-card-pink {
    background: linear-gradient(135deg, #f48fb1 0%, #f06292 100%);
}

.faq-card-title {
    font-size: 20px;
    font-weight: 700;
    color: white;
    margin-bottom: 20px;
    line-height: 1.3;
    text-align: center;  /* Căn giữa tiêu đề */
}

.faq-card-text {
    font-size: 16px;
    line-height: 1.6;
    color: white;
    flex: 1;
    text-align: center;  /* Căn giữa text */
}

.faq-link {
    color: white;
    text-decoration: underline;
    font-weight: 600;
}

.faq-link:hover {
    text-decoration: none;
}

.faq-footer {
    font-size: 17px;
    color: #333;
    margin-bottom: 30px;
}

.faq-footer-link {
    color: #333;
    text-decoration: underline;
    font-weight: 600;
}

.faq-footer-link:hover {
    text-decoration: none;
}

.faq-button {
    background-color: #004d2e;
    color: white;
    border: none;
    padding: 16px 40px;
    font-size: 17px;
    font-weight: 600;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 77, 46, 0.3);
}

.faq-button:hover {
    background-color: #003d24;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 77, 46, 0.4);
}

.faq-button:active {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(0, 77, 46, 0.3);
}

@media (max-width: 1024px) {
    .faq-cards {
        gap: 25px;
    }

    .faq-title {
        font-size: 36px;
    }

    .faq-card {
        padding: 28px 22px;  /* Điều chỉnh cho tablet */
        min-height: 260px;
    }

    .faq-card-title {
        font-size: 19px;
    }

    .faq-card-text {
        font-size: 15px;
    }
}

@media (max-width: 768px) {
    .faq-container {
        padding: 40px 20px;
    }

    .faq-title {
        font-size: 32px;
        margin-bottom: 40px;
    }

    .faq-cards {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .faq-card {
        padding: 25px 20px;  /* Điều chỉnh cho mobile */
        min-height: auto;
    }

    .faq-card-title {
        font-size: 18px;
        margin-bottom: 15px;
    }

    .faq-card-text {
        font-size: 15px;
    }

    .faq-footer {
        font-size: 15px;
    }

    .faq-button {
        padding: 14px 32px;
        font-size: 16px;
    }
}
</style>
@endpush
@section('content')
<div class="app-intro-container">
    <div class="app-intro-section">
        <!-- Left Side -->
        <div class="app-left-side">
            <div class="app-phone-wrapper">
                <div class="app-sparkle sparkle-top">✦</div>
                <img src="assets/hinhUngDung.png" alt="App Interface" style="width: 200px; height: auto;">
            </div>
            <div class="app-shield-wrapper">
                <div class="app-sparkle sparkle-left">★</div>
                <img src="assets/hinhSecure.svg" alt="Security" style="width: 200px; height: auto;">
            </div>
        </div>

        <!-- Center Content -->
        <div class="app-center-content">
            <div class="app-brand">GIỚI THIỆU ỨNG DỤNG</div>
            <h1 class="app-title">Quản lý công việc nhà, dễ dàng hơn bao giờ hết.</h1>
            <p class="app-description">
                bTaskee là ứng dụng cung cấp dịch vụ giúp việc nhà theo giờ nhanh chóng và chuyên nghiệp.
                Chỉ với vài thao tác trên điện thoại, bạn có thể đặt lịch, theo dõi quá trình làm việc, và thanh toán hoàn toàn tự động.
            </p>
            <button class="app-cta-button">Trải nghiệm ngay</button>
        </div>

        <!-- Right Side -->
        <div class="app-right-side">
            <div class="app-avatar-wrapper">
                <img src="assets/hinhUD.png" alt="App Avatar" style="width: 300px; height: auto;">
            </div>
            <div class="app-price-badge">
                <div class="price-label">Chỉ từ</div>
                <div class="price-amount">85.00VND</div>
                <div class="price-period">/giờ</div>
            </div>
            <div class="app-sparkle sparkle-bottom">✦</div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="features-container">
    <h2 class="features-title">Tại sao nên chọn bTaskee?</h2>
    <div class="features-section">
        <!-- Feature 1 -->
        <div class="feature-item">
            <div class="feature-icon">
                <img src="assets/hinhMoney.svg" alt="Money Icon">
            </div>
            <p class="feature-description">
                <strong>Giá cả minh bạch</strong>
                Bạn biết trước chi phí trước khi đặt, không phát sinh thêm.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="feature-item">
            <div class="feature-icon">
                <img src="assets/hinhTB.svg" alt="Notification Icon">
            </div>
            <p class="feature-description">
                <strong>Thông báo thời gian thực</strong>
                Cập nhật trạng thái đơn hàng và quá trình làm việc ngay trên ứng dụng.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="feature-item">
            <div class="feature-icon">
                <img src="assets/hinhNguoi.svg" alt="Person Icon">
            </div>
            <p class="feature-description">
                <strong>Nhân viên thay thế nhanh chóng</strong>
                Nếu cộng tác viên bận, hệ thống sẽ đề xuất người khác ngay lập tức.
            </p>
        </div>
    </div>
</div>

<!-- Target Audience Section -->
<div class="audience-container">
    <div class="audience-wrapper">
        <h2 class="audience-title">Ai nên sử dụng ứng dụng bTaskee?</h2>
        <div class="audience-card">
            <div class="audience-icon-left">
                <img src="assets/iconPeople.svg" alt="People Icon">
            </div>
            <div class="audience-content">
                <ul class="audience-list">
                    <li>Người bận rộn cần dọn dẹp nhanh và linh hoạt.</li>
                    <li>Gia đình muốn thuê giúp việc theo giờ mà không phải gọi điện hay tìm người thủ công.</li>
                    <li>Người thích tự chủ trong việc chọn thời gian, giá cả và người làm phù hợp.</li>
                    <li>Chỉ cần mở ứng dụng trên điện thoại, chọn dịch vụ và thời gian bạn muốn. Mọi bước đặt lịch, theo dõi, và thanh toán đều được thực hiện ngay trong app.</li>
                </ul>
            </div>
            <div class="audience-icon-right">
                <img src="assets/iconSearch.svg" alt="Search Icon">
            </div>
        </div>
    </div>
</div>
<!-- How It Works Section -->
<div class="howwork-container">
    <h2 class="howwork-title">Cách thức hoạt động?</h2>
    <div class="howwork-content">
        <div class="howwork-steps">
            <!-- Step 1 -->
            <div class="step-item">
                <div class="step-number">1.</div>
                <div class="step-text">
                    Tải ứng dụng btaskee từ App Store hoặc Google Play.
                </div>
            </div>

            <!-- Step 2 -->
            <div class="step-item">
                <div class="step-number">2.</div>
                <div class="step-text">
                    Đăng ký tài khoản và điền thông tin của bạn.
                </div>
            </div>

            <!-- Step 3 -->
            <div class="step-item">
                <div class="step-number">3.</div>
                <div class="step-text">
                    Chọn dịch vụ giúp việc theo giờ và thời gian bạn muốn.
                </div>
            </div>

            <!-- Step 4 -->
            <div class="step-item">
                <div class="step-number">4.</div>
                <div class="step-text">
                    Xác nhận đặt lịch và bắt đầu sử dụng dịch vụ ngay trong ứng dụng.
                </div>
            </div>
        </div>

        <div class="howwork-image">
            <img src="assets/hinhUD.png" alt="App Preview">
        </div>
    </div>
</div>
<!-- FAQ Section -->
<div class="faq-container">
    <h2 class="faq-title">Câu hỏi của bạn, <span class="faq-title-italic">được giải đáp.</span></h2>
    <div class="faq-cards">
        <!-- Card 1 -->
        <div class="faq-card faq-card-yellow">
            <h3 class="faq-card-title">Phí dịch vụ được tính như thế nào?</h3>
            <p class="faq-card-text">
                Phí dịch vụ trong ứng dụng bao gồm toàn bộ quyền truy cập các tính năng, xử lý thanh toán và hỗ trợ quản lý. Bạn không phải trả thêm phí cho mỗi lần đặt lịch.
            </p>
        </div>

        <!-- Card 2 -->
        <div class="faq-card faq-card-cyan">
            <h3 class="faq-card-title">Làm sao để hủy đặt dịch vụ?</h3>
            <p class="faq-card-text">
                Bạn có thể hủy trực tiếp trong app chỉ với vài thao tác. Ngoài ra, bạn cũng có thể liên hệ support@btaskee.com nếu cần hỗ trợ.
            </p>
        </div>

        <!-- Card 3 -->
        <div class="faq-card faq-card-pink">
            <h3 class="faq-card-title">Tôi nhận thông báo như thế nào?</h3>
            <p class="faq-card-text">
                Ứng dụng gửi thông báo tức thì cho mọi cập nhật về lịch trình, thay đổi người giúp việc hoặc xác nhận thanh toán.
            </p>
        </div>
    </div>

    <a href="{{ url('/') }}"> <button class="faq-button">Về trang chủ</button> </a>
</div>
@endsection
