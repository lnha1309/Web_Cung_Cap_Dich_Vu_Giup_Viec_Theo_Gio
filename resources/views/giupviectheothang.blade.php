@extends('layouts.base')
@section('title', 'Dịch vụ giúp việc theo tháng')

@push('styles')
<style>
    /* --- 1. Thiết lập chung & Bảng màu --- */
    :root {
        --primary-color: #004d2e;
        --primary-color-dark: #003a22;
        --background-white: #FFFFFF;
        --background-pastel-light: #f0f7f4;
        --background-gray-light: #f8f9fa;
        --text-dark: #333333;
        --text-secondary: #555555;
        --text-light: #FFFFFF;
        --border-color: #e0e0e0;
    }

    body {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        --primary-color-dark: #003a22;
        --background-white: #FFFFFF;
        --background-pastel-light: #f0f7f4;
        --background-gray-light: #f8f9fa;
        --text-dark: #333333;
        --text-secondary: #555555;
        --text-light: #FFFFFF;
        --border-color: #e0e0e0;
    }

    body {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        margin: 0;
        background-color: var(--background-white);
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .monthly-container {
        width: 90%;
        max-width: 1100px;
        margin: 0 auto;
    }

    .monthly-section {
        padding: 70px 0;
    }


    .monthly-section h2 {
        font-size: 2.5rem;
        text-align: center;
        margin-bottom: 50px;
        color: var(--primary-color);
    }

    .monthly-section img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
    }

    .monthly-btn {
        display: inline-block;
        padding: 12px 28px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .monthly-btn-primary {
        background-color: var(--primary-color);
        color: var(--text-light);
        border: 2px solid var(--primary-color);
    }

    .monthly-btn-primary:hover {
        background-color: var(--primary-color-dark);
        border-color: var(--primary-color-dark);
    }

    .monthly-btn-secondary {
        background-color: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
    }

    .monthly-btn-secondary:hover {
        background-color: var(--primary-color);
        color: var(--text-light);
    }

    /* --- 1. Phần Hero (Giữ nguyên) --- */
    .monthly-hero-section {
        padding-top: 50px;
        display: flex;
        align-items: center;
        gap: 40px;
    }

    .monthly-hero-content {
        flex: 1;
    }

    .monthly-hero-content h1 {
        font-size: 3.2rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .monthly-hero-content .tagline {
        font-size: 1.2rem;
        margin-bottom: 30px;
    }

    .monthly-hero-buttons .monthly-btn {
        margin-right: 15px;
    }

    .monthly-hero-image {
        flex: 1;
    }

    .monthly-hero-image img {
        box-shadow: 0 10px 30px rgba(0, 77, 46, 0.1);
    }

    /* --- 2. Dịch vụ này dành cho ai? (Giữ nguyên) --- */
    .monthly-who-for {
        background-color: var(--background-gray-light);
    }

    .monthly-who-for .monthly-container {
        display: flex;
        align-items: center;
        gap: 50px;
    }

    .monthly-who-image {
        flex: 1;
    }

    .monthly-who-content {
        flex: 1;
    }

    .monthly-who-content h2 {
        text-align: left;
    }

    .monthly-who-list {
        list-style: none;
        padding-left: 0;
    }

    .monthly-who-list li {
        font-size: 1.1rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .monthly-who-list .icon {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-right: 15px;
    }

    /* --- 3. Quy trình (Layout xen kẽ, giữ nguyên) --- */
    .monthly-included-row {
        display: flex;
        align-items: center;
        gap: 50px;
        margin-bottom: 60px;
    }

    .monthly-included-row.row-reverse {
        flex-direction: row-reverse;
    }

    .monthly-included-content {
        flex: 1;
    }

    .monthly-included-image-wrapper {
        flex: 1;
    }

    .monthly-included-image-wrapper img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.1);
        border-radius: 12px;
    }

    .monthly-included-content h3 {
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .monthly-included-content ul {
        list-style: none;
        padding-left: 0;
    }

    .monthly-included-content li {
        position: relative;
        padding-left: 30px;
        margin-bottom: 12px;
        font-size: 1.05rem;
    }

    .monthly-included-content li::before {
        content: '✓';
        position: absolute;
        left: 0;
        top: 0;
        color: var(--primary-color);
        font-weight: bold;
        font-size: 1.2rem;
    }

    /* Hộp "Cam kết" - đổi màu xanh lá cây */
    .monthly-not-included {
        margin-top: 40px;
        background-color: var(--background-pastel-light);
        border: 1px solid #cce0cc;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
    }

    .monthly-not-included h4 {
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    /* --- 4. Bảng giá --- */
    .monthly-pricing-intro {
        text-align: center;
        max-width: 600px;
        margin: 0 auto 40px auto;
    }

    .monthly-pricing-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .monthly-pricing-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        background-color: var(--background-white);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    /* Chỉ hiệu ứng hover nhẹ, KHÔNG có màu xanh mặc định */
    .monthly-pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.1);
    }

    /* Class mới cho card được chọn */
    .monthly-pricing-card.selected {
        border-color: var(--primary-color);
        border-width: 2px;
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.15);
    }

    .monthly-pricing-card h3 {
        font-size: 1.5rem;
        color: var(--primary-color);
    }

    .monthly-price {
        font-size: 2.8rem;
        font-weight: bold;
        color: var(--text-dark);
        margin: 15px 0;
    }

    .monthly-price span {
        font-size: 1rem;
        font-weight: normal;
        color: var(--text-secondary);
    }

    .monthly-pricing-card ul {
        list-style: none;
        padding: 0;
        margin: 20px 0;
        text-align: left;
    }

    .monthly-pricing-card li {
        margin-bottom: 10px;
        padding-left: 25px;
        position: relative;
    }

    .monthly-pricing-card li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--primary-color);
    }

    .monthly-pricing-card .monthly-btn {
        margin-top: auto;
    }

    /* --- Responsive (Giữ nguyên) --- */
    @media (max-width: 992px) {
        .monthly-section h2 {
            font-size: 2.2rem;
        }

        .monthly-hero-section {
            flex-direction: column;
            text-align: center;
        }

        .monthly-hero-content h1 {
            font-size: 2.8rem;
        }

        .monthly-who-for .monthly-container {
            flex-direction: column;
        }

        .monthly-who-content h2 {
            text-align: center;
        }

        .monthly-included-row {
            flex-direction: column !important;
            gap: 30px;
            margin-bottom: 40px;
        }

        .monthly-included-content h3 {
            text-align: center;
        }

        .monthly-pricing-grid {
            grid-template-columns: 1fr;
        }

        .monthly-pricing-card {
            max-width: 400px;
            margin: 0 auto;
        }
    }

    @media (max-width: 768px) {
        .monthly-section h2 {
            font-size: 2rem;
        }

        .monthly-hero-section {
            padding-top: 20px;
        }

        .monthly-hero-content h1 {
            font-size: 2.5rem;
        }

        .monthly-hero-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pricingCards = document.querySelectorAll('.monthly-pricing-card');

        pricingCards.forEach(card => {
            const button = card.querySelector('.monthly-btn');

            button.addEventListener('click', function(e) {
                e.preventDefault();

                // Bỏ class 'selected' khỏi tất cả các card
                pricingCards.forEach(c => c.classList.remove('selected'));

                // Thêm class 'selected' vào card được click
                card.classList.add('selected');

                // Lấy tên gói để xử lý
                const packageName = card.querySelector('h3').textContent;
                console.log('Đã chọn gói:', packageName);

                // Có thể thêm logic chuyển trang hoặc mở form tư vấn ở đây
                // window.location.href = "{{ url('contact-form') }}?package=" + encodeURIComponent(packageName);
            });
        });
    });
</script>
@endpush

@section('content')
<section class="monthly-section monthly-hero-section monthly-container">
    <div class="monthly-hero-content">
        <h1>Dịch vụ Giúp việc theo tháng</h1>
        <p class="tagline">An tâm trọn vẹn với nhân viên ổn định, gắn bó lâu dài. Giải pháp chăm sóc gia đình toàn diện.</p>
        <div class="monthly-hero-buttons">
            <a href="#" class="monthly-btn monthly-btn-primary">Đăng ký tư vấn</a>
            <a href="#pricing" class="monthly-btn monthly-btn-secondary">Xem gói dịch vụ</a>
        </div>
    </div>
    <div class="monthly-hero-image">
        <img src="{{ asset('assets/giupviecthang-hero.jpg') }}" alt="Nhân viên giúp việc theo tháng đáng tin cậy">
    </div>
</section>

<section class="monthly-section monthly-who-for">
    <div class="monthly-container">
        <div class="monthly-who-image">
            <img src="{{ asset('assets/giupviecthang-who-for.webp') }}" alt="Gia đình hạnh phúc có con nhỏ">
        </div>
        <div class="monthly-who-content">
            <h2>Phù hợp với gia đình nào?</h2>
            <ul class="monthly-who-list">
                <li><span class="icon">✓</span>Gia đình có con nhỏ, bận rộn cần người hỗ trợ đều đặn.</li>
                <li><span class="icon">✓</span>Mong muốn có nhân viên quen thuộc, tin cậy, hiểu rõ thói quen gia đình.</li>
                <li><span class="icon">✓</span>Cần người phụ giúp các công việc hàng ngày (nấu ăn, giặt ủi...).</li>
                <li><span class="icon">✓</span>Chủ nhà muốn có sự ổn định, không phải lo tìm người mới liên tục.</li>
            </ul>
        </div>
    </div>
</section>

<section class="monthly-section monthly-included monthly-container">
    <h2>Dịch vụ bao gồm những gì?</h2>

    <div class="monthly-included-row">
        <div class="monthly-included-content">
            <h3>Phòng khách / Phòng ngủ</h3>
            <ul>
                <li>Quét, hút bụi và lau sàn nhà.</li>
                <li>Lau bụi bẩn trên các bề mặt (bàn, ghế, tủ, kệ).</li>
                <li>Vệ sinh bên ngoài các thiết bị (TV, quạt, máy lạnh).</li>
                <li>Sắp xếp lại gối, chăn, ga trải giường (không thay).</li>
                <li>Thu dọn rác.</li>
            </ul>
        </div>
        <div class="monthly-included-image-wrapper">
            <img src="{{ asset('assets/included-phongkhach.webp') }}" alt="Phòng khách sạch sẽ">
        </div>
    </div>

    <div class="monthly-included-row row-reverse">
        <div class="monthly-included-content">
            <h3>Nhà bếp</h3>
            <ul>
                <li>Rửa sạch chén bát (cho 1 bữa ăn gần nhất).</li>
                <li>Lau sạch bề mặt bếp, bồn rửa.</li>
                <li>Vệ sinh bên ngoài các thiết bị (tủ lạnh, lò vi sóng, bếp).</li>
                <li>Lau bàn ăn.</li>
                <li>Thu dọn rác.</li>
            </ul>
        </div>
        <div class="monthly-included-image-wrapper">
            <img src="{{ asset('assets/included-nhabep.jpg') }}" alt="Nhà bếp gọn gàng">
        </div>
    </div>

    <div class="monthly-included-row">
        <div class="monthly-included-content">
            <h3>Nhà vệ sinh</h3>
            <ul>
                <li>Cọ rửa bồn cầu, bồn rửa mặt, vòi sen/bồn tắm.</li>
                <li>Lau chùi gương.</li>
                <li>Lau sàn nhà vệ sinh.</li>
            </ul>
        </div>
        <div class="monthly-included-image-wrapper">
            <img src="{{ asset('assets/included-nhavesinh.jpg') }}" alt="Nhà vệ sinh sáng bóng">
        </div>
    </div>

    <div class="monthly-not-included">
        <h4>Lưu ý: Dịch vụ KHÔNG bao gồm:</h4>
        <p>Giặt ủi, phơi, gấp quần áo; Nấu ăn; Vệ sinh bên trong các thiết bị (tủ lạnh, lò nướng); Lau kính trên cao; Di chuyển đồ đạc nặng; Vệ sinh sau xây dựng.</p>
    </div>
</section>

<section id="pricing" class="monthly-section monthly-pricing monthly-container">
    <h2>Các gói dịch vụ theo tháng</h2>
    <p class="monthly-pricing-intro">Các gói dịch vụ được thiết kế linh hoạt theo nhu cầu thực tế của gia đình. Vui lòng liên hệ để nhận báo giá chi tiết và chính xác nhất.</p>
    <div class="monthly-pricing-grid">
        <div class="monthly-pricing-card">
            <h3>Gói Nửa ngày (4 tiếng)</h3>
            <div class="monthly-price">Liên hệ</div>
            <p>Sáng hoặc chiều (T2 - T7). Phù hợp dọn dẹp, nấu 1 bữa.</p>
            <ul>
                <li>Làm 4 tiếng/ngày</li>
                <li>Dọn dẹp & Nấu ăn (tùy chọn)</li>
                <li>Hợp đồng từ 6 tháng</li>
            </ul>
            <a href="#" class="monthly-btn monthly-btn-secondary">Yêu cầu tư vấn</a>
        </div>

        <div class="monthly-pricing-card">
            <h3>Gói Toàn thời gian (8 tiếng)</h3>
            <div class="monthly-price">Liên hệ</div>
            <p>Giờ hành chính (T2 - T7). Chăm sóc toàn diện cho gia đình.</p>
            <ul>
                <li>Làm 8 tiếng/ngày</li>
                <li>Bao gồm toàn bộ việc nhà</li>
                <li>Nấu 2 bữa (trưa/tối)</li>
                <li>Hợp đồng từ 12 tháng</li>
            </ul>
            <a href="#" class="monthly-btn monthly-btn-secondary">Yêu cầu tư vấn</a>
        </div>

        <div class="monthly-pricing-card">
            <h3>Gói Tùy chỉnh</h3>
            <div class="monthly-price">Linh hoạt</div>
            <p>Thiết kế gói theo nhu cầu (vd: 3 buổi/tuần, chỉ nấu ăn...).</p>
            <ul>
                <li>Chọn số buổi/tuần</li>
                <li>Chọn giờ làm linh hoạt</li>
                <li>Chọn công việc cụ thể</li>
            </ul>
            <a href="#" class="monthly-btn monthly-btn-secondary">Yêu cầu tư vấn</a>
        </div>
    </div>
</section>
@endsection