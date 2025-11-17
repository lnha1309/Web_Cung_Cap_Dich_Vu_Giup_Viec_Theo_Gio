@extends('layouts.base')
@section('title', 'Trở thành nhân viên bTaskee')
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
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    .main-section {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 60px;
        padding: 20px 0;
    }

    .content {
        flex: 1;
        max-width: 500px;
    }

    h1 {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 30px;
        color: #1a1a1a;
        line-height: 1.2;
    }

    .subtitle {
        font-size: 18px;
        color: #333;
        margin-bottom: 40px;
        line-height: 1.6;
    }

    .benefits {
        list-style: none;
    }

    .benefits li {
        font-size: 18px;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        line-height: 1.5;
    }

    .benefits li::before {
        content: '✓';
        font-size: 24px;
        color: #004d2e;
        font-weight: bold;
        margin-right: 15px;
        flex-shrink: 0;
    }

    .benefits strong {
        font-weight: 600;
        color: #1a1a1a;
        display: inline-block;
        margin: 0 4px;
    }

    .image-section {
        flex: 1;
        max-width: 550px;
    }

    .image-section img {
        width: 100%;
        height: auto;
        border-radius: 16px;
    }

    .stats-section {
        max-width: 1400px;
        margin: 30px auto 0;
        padding: 8px 60px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 60px;
        background-color: #F8F1E3;
        border-radius: 50px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon img {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }

    .stat-text {
        font-size: 16px;
        color: #333;
        line-height: 1.5;
    }

    .job-container {
        background: #d7ecff;
        border-radius: 40px;
        max-width: 1200px;
        margin: 48px auto 0;
        padding: 64px 32px 48px 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .job-title {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 36px;
    }

    .job-pics-row {
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
        margin-bottom: 44px;
    }

    .job-pic {
        flex: 1;
        min-width: 240px;
        max-width: 320px;
        text-align: center;
        background: rgba(255, 255, 255, 0.0);
        border-radius: 18px;
        padding: 0 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .job-pic img {
        width: 100%;
        border-radius: 16px;
    }

    .job-pic h3 {
        font-size: 20px;
        margin: 18px 0 10px;
        font-weight: bold;
        color: #1a1a1a;
    }

    .job-desc {
        font-size: 16px;
        color: #222;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .job-apply-btn {
        background: #228455;
        color: white;
        font-size: 15px;
        padding: 14px 30px;
        border: none;
        border-radius: 32px;
        font-weight: 500;
        cursor: pointer;
        margin-top: -20px;
    }

    .cook-container {
        background: #CBE2D9;
        border-radius: 40px;
        max-width: 1200px;
        margin: 48px auto 0;
        padding: 64px 32px 48px 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .cook-title {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 36px;
    }

    .cook-pics-row {
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .cook-pic {
        flex: 1;
        min-width: 240px;
        max-width: 420px;
        text-align: center;
        background: rgba(255, 255, 255, 0.0);
        border-radius: 18px;
        padding: 0 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .cook-pic img {
        width: 100%;
        border-radius: 16px;
    }

    .cook-pic h3 {
        font-size: 20px;
        margin: 18px 0 10px;
        font-weight: bold;
        color: #1a1a1a;
    }

    .cook-desc {
        font-size: 16px;
        color: #222;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .cleaner-container {
        background: #e4f6f4;
        border-radius: 40px;
        max-width: 1200px;
        margin: 48px auto 0;
        padding: 64px 32px 48px 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .cleaner-title {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 36px;
    }

    .cleaner-pics-row {
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
        margin-bottom: 44px;
    }

    .cleaner-pic {
        flex: 1;
        min-width: 240px;
        max-width: 420px;
        text-align: center;
        background: rgba(255, 255, 255, 0.0);
        border-radius: 18px;
        padding: 0 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .cleaner-pic img {
        width: 100%;
        border-radius: 16px;
    }

    .cleaner-pic h3 {
        font-size: 20px;
        margin: 18px 0 10px;
        font-weight: bold;
        color: #1a1a1a;
    }

    .cleaner-desc {
        font-size: 16px;
        color: #222;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .laundry-container {
        background: #ffe4f7;
        border-radius: 40px;
        max-width: 1200px;
        margin: 48px auto 0;
        padding: 64px 32px 48px 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .laundry-title {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 36px;
    }

    .laundry-pics-row {
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
        margin-bottom: 44px;
    }

    .laundry-pic {
        flex: 1;
        min-width: 240px;
        max-width: 420px;
        text-align: center;
        background: rgba(255, 255, 255, 0.0);
        border-radius: 18px;
        padding: 0 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .laundry-pic img {
        width: 100%;
        border-radius: 16px;
    }

    .laundry-pic h3 {
        font-size: 20px;
        margin: 18px 0 10px;
        font-weight: bold;
        color: #1a1a1a;
    }

    .laundry-desc {
        font-size: 16px;
        color: #222;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .requirements-container {
        max-width: 1200px;
        background-color: #F8F1E3;
        border-radius: 40px;
        padding: 48px 40px;
        margin: 48px auto 0;
        box-sizing: border-box;
    }

    .requirements-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
        text-align: center;
        margin-bottom: 32px;
    }

    .requirements-list {
        list-style: none;
        padding-left: 0;
        font-size: 18px;
        color: #333;
        line-height: 1.6;
        width: fit-content;
        margin: 0 auto;
        text-align: left;
    }

    .requirements-list li {
        position: relative;
        padding-left: 32px;
        margin-bottom: 16px;
    }

    .requirements-list li::before {
        content: '✓';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        color: #004d2e;
        font-weight: bold;
        font-size: 18px;
    }

    .partner-privileges-container {
        max-width: 1200px;
        margin: 48px auto 0;
        padding: 0 20px 48px 20px;
        text-align: center;
    }

    .partner-privileges-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 24px;
        text-align: left;
    }

    .partner-image img {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 40px;
    }

    .privileges-list {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
    }

    .privilege-item {
        flex: 1;
        min-width: 220px;
        max-width: 280px;
        text-align: left;
    }

    .privilege-icon {
        width: 40px;
        height: 40px;
        margin-bottom: 12px;
    }

    .privilege-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .privilege-text h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 8px;
        color: #000;
    }

    .privilege-text p {
        font-size: 16px;
        color: #333;
        line-height: 1.4;
    }

    .register-steps-container {
        background-color: #F8F1E3;
        margin: 48px auto 0;
        padding: 60px 20px 60px 20px;
        text-align: center;
        width: 100%
    }

    .register-steps-title {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a1a;
        text-align: left;
        margin-bottom: 40px;
    }

    .steps-bar {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        gap: 40px;
        padding: 0 20px;
    }

    .step-item {
        flex: 1 1 0;
        max-width: 250px;
        position: relative;
        text-align: center;
    }

    .step-item h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 16px;
        position: relative;
        padding-top: 28px;
    }

    .step-item h3::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 12px;
        height: 12px;
        background-color: #000;
        border-radius: 50%;
    }

    .step-item p {
        font-size: 16px;
        color: #333;
        line-height: 1.5;
    }

    .steps-bar::before {
        content: '';
        position: absolute;
        top: 6px;
        left: 60px;
        right: 60px;
        height: 2px;
        background-color: #ccc;
        z-index: 0;
    }

    /* Apply CTA between stats and requirements */
    .apply-cta-wrapper {
        max-width: 1200px;
        margin: 24px auto 0;
        display: flex;
        justify-content: center;
    }

    .apply-cta-btn {
        background: #004d2e;
        color: #fff;
        padding: 14px 32px;
        border: 2px solid #004d2e;
        border-radius: 32px;
        font-size: 18px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.2s ease;
    }

    .apply-cta-btn:hover {
        background-color: #1f4432;
    }

    @media (max-width: 968px) {
        .main-section {
            flex-direction: column;
            padding: 20px 0;
        }

        .content {
            max-width: 100%;
        }

        .image-section {
            max-width: 100%;
        }

        h1 {
            font-size: 36px;
        }

        .stats-section {
            flex-direction: column;
            padding: 30px 20px;
            gap: 30px;
            border-radius: 30px;
        }

        .stat-item {
            width: 100%;
            justify-content: center;
        }

        .job-container {
            padding: 30px 12px 28px 12px;
            border-radius: 22px;
        }

        .job-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .job-pics-row {
            gap: 20px;
        }

        .job-pic {
            min-width: 160px;
        }

        .cook-container {
            padding: 30px 12px 28px 12px;
            border-radius: 22px;
        }

        .cook-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .cook-pics-row {
            gap: 20px;
        }

        .cook-pic {
            min-width: 160px;
        }

        .cleaner-container {
            padding: 30px 12px 28px 12px;
            border-radius: 22px;
        }

        .cleaner-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .cleaner-pics-row {
            gap: 20px;
        }

        .cleaner-pic {
            min-width: 160px;
        }

        .laundry-container {
            padding: 30px 12px 28px 12px;
            border-radius: 22px;
        }

        .laundry-title {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .laundry-pics-row {
            gap: 20px;
        }

        .laundry-pic {
            min-width: 160px;
        }

        .privileges-list {
            flex-direction: column;
            align-items: center;
        }

        .privilege-item {
            max-width: 100%;
            margin-bottom: 32px;
            text-align: center;
        }

        .privilege-text h3 {
            font-size: 20px;
        }

        .steps-bar {
            flex-direction: column;
            align-items: center;
        }

        .step-item {
            max-width: 100%;
            margin-bottom: 40px;
        }

        .steps-bar::before {
            display: none;
        }

        .step-item:last-child {
            margin-bottom: 0;
        }
    }
</style>
@endpush
@section('content')

<body>
    <div class="main-section">
        <div class="content">
            <h1>Tại sao lại trở thành nhân viên bTaskee?</h1>
            <p class="subtitle">Làm việc cùng một công ty có hơn 10 năm kinh nghiệm trong lĩnh vực cung cấp dịch vụ cho khách hàng.</p>

            <ul class="benefits">
                <li>Nhận <strong>công việc hàng tuần</strong></li>
                <li>Tự do chọn <strong>thời gian</strong> và <strong>khu vực</strong> làm việc</li>
                <li>Ứng dụng đặt lịch<strong> không tốn dữ liệu </strong></li>
                <li>Thu nhập trung bình <strong>cao hơn 42%</strong>mức lương tối thiểu</li>
            </ul>
        </div>
        <div class="image-section">
            <img src="assets/hinhGTNV.png" alt="SweepStar Workers">
        </div>
    </div>

    <div class="stats-section">
        <div class="stat-item">
            <div class="stat-icon">
                <img src="assets/iconNha.svg" alt="House icon">
            </div>
            <div class="stat-text">Hơn 10 năm phục vụ khách hàng</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">
                <img src="assets/iconNguoi.svg" alt="People icon">
            </div>
            <div class="stat-text">Hơn 50,000 công việc đã được tạo ra</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">
                <img src="assets/iconTien.svg" alt="Money icon">
            </div>
            <div class="stat-text">Thu nhập trung bình cao hơn 42% so với lương tối thiểu</div>
        </div>
    </div>

    <div class="apply-cta-wrapper">
        <a href="{{ url('/apply') }}" class="apply-cta-btn">Đăng ký ngay</a>
    </div>

    <div class="requirements-container">
        <h2 class="requirements-title">Để trở thành nhân viên bạn phải đáp ứng các yêu cầu sau:</h2>
        <ul class="requirements-list">
            <li>Có điện thoại di động thông minh.</li>
            <li>Có phương tiện xe máy di chuyển.</li>
            <li>Có ít nhất 01 năm kinh nghiệm về dọn dẹp nhà.</li>
            <li>Lý lịch trong sạch</li>
            <li>Ưu tiên ứng viên có kinh nghiệm làm người giúp việc lâu năm.</li>
            <li>Kỹ năng giao tiếp căn bản.</li>
        </ul>
    </div>

    <div class="partner-privileges-container">
        <h2 class="partner-privileges-title">Đặc quyền của đối tác</h2>
        <div class="partner-image">
            <img src="assets/hinhDoiTac.png" alt="Đặc quyền của đối tác">
        </div>
        <div class="privileges-list">
            <div class="privilege-item">
                <div class="privilege-icon">
                    <img src="assets/iconHuman.png" alt="Không phụ thuộc vào một khách hàng">
                </div>
                <div class="privilege-text">
                    <h3>Không bị phụ thuộc vào một khách hàng</h3>
                    <p>Bạn chỉ cần nhận việc trên ứng dụng mà không cần phải tìm kiếm khách hàng.</p>
                </div>
            </div>
            <div class="privilege-item">
                <div class="privilege-icon">
                    <img src="assets/iconGLV.png" alt="Linh động về thời gian">
                </div>
                <div class="privilege-text">
                    <h3>Linh động về thời gian</h3>
                    <p>Bạn có thể chủ động lựa chọn những công việc phù hợp với mình (về thời gian, địa điểm hay giá tiền).</p>
                </div>
            </div>
            <div class="privilege-item">
                <div class="privilege-icon">
                    <img src="assets/iconMoney.png" alt="Thu nhập cao">
                </div>
                <div class="privilege-text">
                    <h3>Thu nhập cao</h3>
                    <p>Thu nhập lên đến 20 triệu/tháng nếu bạn tích cực và siêng năng theo dõi nhận việc.</p>
                </div>
            </div>
            <div class="privilege-item">
                <div class="privilege-icon">
                    <img src="assets/iconShield.png" alt="Đảm bảo quyền lợi lao động">
                </div>
                <div class="privilege-text">
                    <h3>Đảm bảo quyền lợi lao động</h3>
                    <p>Được hưởng chính sách hỗ trợ của bTaskee lên đến 100 triệu đồng.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="register-steps-container">
        <h2 class="register-steps-title">Các bước đăng ký</h2>
        <div class="steps-bar">
            <div class="step-item">
                <h3>Bước 1</h3>
                <p>Tải ứng dụng bTaskee Partner, hoàn thành bài kiểm tra tổng hợp về kiến thức liên quan đến công việc</p>
            </div>
            <div class="step-item">
                <h3>Bước 2</h3>
                <p>Nộp hồ sơ và phỏng vấn tại văn phòng</p>
            </div>
            <div class="step-item">
                <h3>Bước 3</h3>
                <p>Hoàn thành tốt lần thử việc đầu tiên</p>
            </div>
            <div class="step-item">
                <h3>Bước 4</h3>
                <p>Trở thành đối tác của bTaskee và bắt đầu nhận việc</p>
            </div>
        </div>
    </div>
    @endsection