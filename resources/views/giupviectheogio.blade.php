@extends('layouts.base')
@section('title', 'Dịch vụ Giúp việc theo giờ')
@section('global_styles')
<link rel="stylesheet" href="{{ asset('css/header-footer.css') }}">
@endsection
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
        margin: 0;
        background-color: var(--background-white);
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .container {
        width: 90%;
        max-width: 1100px;
        margin: 0 auto;
    }

    section {
        padding: 70px 0;
    }

    h2 {
        font-size: 2.5rem;
        text-align: center;
        margin-bottom: 50px;
        color: var(--primary-color);
    }

    img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
    }

    /* Nút bấm chung */
    .btn {
        display: inline-block;
        padding: 12px 28px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-primary {
        background-color: var(--primary-color);
        color: var(--text-light);
        border: 2px solid var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--primary-color-dark);
        border-color: var(--primary-color-dark);
    }

    .btn-secondary {
        background-color: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
    }

    .btn-secondary:hover {
        background-color: var(--primary-color);
        color: var(--text-light);
    }

    /* --- 1. Phần Hero (Split Layout) --- */
    .hero-section {
        padding-top: 50px;
        display: flex;
        align-items: center;
        gap: 40px;
    }

    .hero-content {
        flex: 1;
    }

    .hero-content h1 {
        font-size: 3.2rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .hero-content .tagline {
        font-size: 1.2rem;
        margin-bottom: 30px;
    }

    .hero-buttons .btn {
        margin-right: 15px;
    }

    .hero-image {
        flex: 1;
    }

    .hero-image img {
        box-shadow: 0 10px 30px rgba(0, 77, 46, 0.1);
    }

    /* --- 2. Dịch vụ này dành cho ai? --- */
    .who-for {
        background-color: var(--background-gray-light);
    }

    .who-for .container {
        display: flex;
        align-items: center;
        gap: 50px;
    }

    .who-image {
        flex: 1;
    }

    .who-content {
        flex: 1;
    }

    .who-content h2 {
        text-align: left;
    }

    .who-list {
        list-style: none;
        padding-left: 0;
    }

    .who-list li {
        font-size: 1.1rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .who-list .icon {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-right: 15px;
    }

    /* --- 3. Danh Sách Công Việc (Layout xen kẽ) --- */
    .included-row {
        display: flex;
        align-items: center;
        gap: 50px;
        margin-bottom: 60px;
    }

    .included-row.row-reverse {
        flex-direction: row-reverse;
    }

    .included-content {
        flex: 1;
    }

    .included-image-wrapper img {
        width: 600px;
        height: 350px;
        object-fit: cover;
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.1);
        border-radius: 12px;
    }

    .included-content h3 {
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .included-content ul {
        list-style: none;
        padding-left: 0;
    }

    .included-content li {
        position: relative;
        padding-left: 30px;
        margin-bottom: 12px;
        font-size: 1.05rem;
    }

    .included-content li::before {
        content: '✓';
        position: absolute;
        left: 0;
        top: 0;
        color: var(--primary-color);
        font-weight: bold;
        font-size: 1.2rem;
    }

    .not-included {
        margin-top: 40px;
        background-color: #fff8f8;
        border: 1px solid #ffe0e0;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
    }

    .not-included h4 {
        color: #d9534f;
        margin-bottom: 10px;
    }

    @media (max-width: 992px) {
        .included-row {
            flex-direction: column !important;
            gap: 30px;
            margin-bottom: 40px;
        }

        .included-content h3 {
            text-align: center;
        }
    }

    /* --- 4. Quy trình đặt dịch vụ --- */
    .how-it-works {
        background-color: var(--background-pastel-light);
    }

    .steps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
        text-align: center;
    }

    .step img {
        width: 120px;
        height: 120px;
        margin-bottom: 20px;
    }

    .step h4 {
        font-size: 1.3rem;
        color: var(--primary-color);
    }

    /* --- 5. Bảng giá --- */
    .pricing-intro {
        text-align: center;
        max-width: 600px;
        margin: 0 auto 40px auto;
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .pricing-card {
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
    .pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.1);
    }

    /* Class mới cho card được chọn */
    .pricing-card.selected {
        border-color: var(--primary-color);
        border-width: 2px;
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.15);
    }

    .pricing-card h3 {
        font-size: 1.5rem;
        color: var(--primary-color);
    }

    .price {
        font-size: 2.8rem;
        font-weight: bold;
        color: var(--text-dark);
        margin: 15px 0;
    }

    .price span {
        font-size: 1rem;
        font-weight: normal;
        color: var(--text-secondary);
    }

    .pricing-card ul {
        list-style: none;
        padding: 0;
        margin: 20px 0;
        text-align: left;
    }

    .pricing-card li {
        margin-bottom: 10px;
        padding-left: 25px;
        position: relative;
    }

    .pricing-card li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--primary-color);
    }
    
    .pricing-card .btn {
        margin-top: auto;
    }

    /* --- Responsive cho di động --- */
    @media (max-width: 992px) {
        h2 {
            font-size: 2.2rem;
        }

        .hero-section {
            flex-direction: column;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 2.8rem;
        }

        .who-for .container {
            flex-direction: column;
        }

        .who-content h2 {
            text-align: center;
        }

        .pricing-grid {
            grid-template-columns: 1fr;
        }

        .pricing-card {
            max-width: 400px;
            margin: 0 auto;
        }
    }

    @media (max-width: 768px) {
        h2 {
            font-size: 2rem;
        }

        .hero-section {
            padding-top: 20px;
        }

        .hero-content h1 {
            font-size: 2.5rem;
        }

        .hero-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .steps-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricingCards = document.querySelectorAll('.pricing-card');
    const selectAddressUrl = "{{ route('booking.selectAddress') }}";
    
    pricingCards.forEach(card => {
        const button = card.querySelector('.btn');
        if (!button) return;
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Bỏ class 'selected' khỏi tất cả các card
            pricingCards.forEach(c => c.classList.remove('selected'));
            
            // Thêm class 'selected' vào card được click
            card.classList.add('selected');
            
            const hours = parseInt(card.getAttribute('data-duration') || this.getAttribute('data-duration') || '', 10);
            const target = new URL(selectAddressUrl, window.location.origin);
            if (hours) {
                target.searchParams.set('duration', hours);
            }

            window.location.href = target.toString();
        });
    });
});
</script>
@endpush

@section('content')
<section class="hero-section container">
    <div class="hero-content">
        <h1>Dịch vụ Giúp việc theo giờ</h1>
        <p class="tagline">Linh hoạt, tin cậy, cho không gian sống luôn sạch tinh tươm. Bạn chọn giờ, chúng tôi lo việc nhà.</p>
        <div class="hero-buttons">
            <a href="{{ url('select-address') }}" class="btn btn-primary">Đặt lịch ngay</a>
            <a href="#pricing" class="btn btn-secondary">Xem bảng giá</a>
        </div>
    </div>
    <div class="hero-image">
        <img src="{{ asset('assets/hero-image.jpg') }}" alt="Nhân viên giúp việc theo giờ đang dọn dẹp">
    </div>
</section>

<section class="who-for">
    <div class="container">
        <div class="who-image">
            <img src="{{ asset('assets/who-for.jfif') }}" alt="Người phụ nữ thư giãn trên sofa">
        </div>
        <div class="who-content">
            <h2>Dịch vụ này dành cho ai?</h2>
            <ul class="who-list">
                <li><span class="icon">✓</span>Người bận rộn với công việc, không có thời gian dọn dẹp.</li>
                <li><span class="icon">✓</span>Gia đình trẻ cần thêm thời gian chăm sóc con cái, nghỉ ngơi.</li>
                <li><span class="icon">✓</span>Người sống một mình muốn dọn dẹp căn hộ nhanh chóng.</li>
                <li><span class="icon">✓</span>Bất cứ ai muốn tận hưởng cuối tuần thảnh thơi mà nhà cửa vẫn sạch sẽ.</li>
            </ul>
        </div>
    </div>
</section>

<section class="included container">
    <h2>Dịch vụ bao gồm những gì?</h2>

    <div class="included-row">
        <div class="included-content">
            <h3>Phòng khách / Phòng ngủ</h3>
            <ul>
                <li>Quét, hút bụi và lau sàn nhà.</li>
                <li>Lau bụi bẩn trên các bề mặt (bàn, ghế, tủ, kệ).</li>
                <li>Vệ sinh bên ngoài các thiết bị (TV, quạt, máy lạnh).</li>
                <li>Sắp xếp lại gối, chăn, ga trải giường (không thay).</li>
                <li>Thu dọn rác.</li>
            </ul>
        </div>
        <div class="included-image-wrapper">
            <img src="{{ asset('assets/included-phongkhach.webp') }}" alt="Phòng khách sạch sẽ">
        </div>
    </div>

    <div class="included-row row-reverse">
        <div class="included-content">
            <h3>Nhà bếp</h3>
            <ul>
                <li>Rửa sạch chén bát (cho 1 bữa ăn gần nhất).</li>
                <li>Lau sạch bề mặt bếp, bồn rửa.</li>
                <li>Vệ sinh bên ngoài các thiết bị (tủ lạnh, lò vi sóng, bếp).</li>
                <li>Lau bàn ăn.</li>
                <li>Thu dọn rác.</li>
            </ul>
        </div>
        <div class="included-image-wrapper">
            <img src="{{ asset('assets/included-nhabep.jpg') }}" alt="Nhà bếp gọn gàng">
        </div>
    </div>

    <div class="included-row">
        <div class="included-content">
            <h3>Nhà vệ sinh</h3>
            <ul>
                <li>Cọ rửa bồn cầu, bồn rửa mặt, vòi sen/bồn tắm.</li>
                <li>Lau chùi gương.</li>
                <li>Lau sàn nhà vệ sinh.</li>
            </ul>
        </div>
        <div class="included-image-wrapper">
            <img src="{{ asset('assets/included-nhavesinh.jpg') }}" alt="Nhà vệ sinh sáng bóng">
        </div>
    </div>

    <div class="not-included">
        <h4>Lưu ý: Dịch vụ KHÔNG bao gồm:</h4>
        <p>Giặt ủi, phơi, gấp quần áo; Nấu ăn; Vệ sinh bên trong các thiết bị (tủ lạnh, lò nướng); Lau kính trên cao; Di chuyển đồ đạc nặng; Vệ sinh sau xây dựng.</p>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <h2>Chỉ với 3 bước đơn giản</h2>
        <div class="steps-grid">
            <div class="step">
                <img src="{{ asset('assets/step1.png') }}" alt="Biểu tượng lịch">
                <h4>1. Chọn lịch hẹn</h4>
                <p>Chọn ngày, giờ và số giờ bạn cần. Hệ thống luôn cập nhật lịch trống.</p>
            </div>
            <div class="step">
                <img src="{{ asset('assets/step2.png') }}" alt="Biểu tượng ngôi nhà">
                <h4>2. Nhập thông tin</h4>
                <p>Cung cấp địa chỉ của bạn và bất kỳ ghi chú đặc biệt nào cho nhân viên.</p>
            </div>
            <div class="step">
                <img src="{{ asset('assets/step3.png') }}" alt="Biểu tượng nhân viên dọn dẹp">
                <h4>3. Xác nhận & Thư giãn</h4>
                <p>Chúng tôi xác nhận lịch và nhân viên sẽ có mặt đúng hẹn. Tận hưởng nhà sạch!</p>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="pricing container">
    <h2>Bảng giá dịch vụ</h2>
    <p class="pricing-intro">Chúng tôi cam kết một mức giá minh bạch, không phát sinh phụ phí. Bạn chỉ trả tiền cho thời gian bạn sử dụng.</p>
    <div class="pricing-grid">
        <div class="pricing-card" data-duration="2">
            <h3>Gói 2 giờ</h3>
            <div class="price">192.000đ</div>
            <p>Lý tưởng cho căn hộ studio hoặc 1 phòng ngủ.</p>
            <ul>
                <li>Dọn dẹp cơ bản</li>
                <li>Tập trung 1-2 khu vực</li>
            </ul>
            <a href="{{ route('booking.selectAddress', ['duration' => 2]) }}" class="btn btn-secondary" data-duration="2">Chọn gói này</a>
        </div>

        <div class="pricing-card" data-duration="3">
            <h3>Gói 3 giờ</h3>
            <div class="price">240.000đ</div>
            <p>Phổ biến nhất! Phù hợp cho nhà 2 phòng ngủ.</p>
            <ul>
                <li>Dọn dẹp toàn diện</li>
                <li>Đủ thời gian cho các khu vực</li>
            </ul>
            <a href="{{ route('booking.selectAddress', ['duration' => 3]) }}" class="btn btn-secondary" data-duration="3">Chọn gói này</a>
        </div>

        <div class="pricing-card" data-duration="4">
            <h3>Gói 4 giờ</h3>
            <div class="price">320.000đ</div>
            <p>Dành cho nhà lớn, hoặc cần dọn dẹp kỹ.</p>
            <ul>
                <br>
                <li>Dọn dẹp sâu, chi tiết</li>
                <li>Bao quát toàn bộ nhà</li>
            </ul>
            <a href="{{ route('booking.selectAddress', ['duration' => 4]) }}" class="btn btn-secondary" data-duration="4">Chọn gói này</a>
        </div>
    </div>
</section>
@endsection
