@extends('layouts.base')
@section('title', 'Dịch vụ Giúp việc theo tháng')
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

    /* --- 1. Phần Hero --- */
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

    /* --- 3. Quy trình / Hạng mục công việc --- */
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
    .included-image-wrapper {
        flex: 1;
    }
    .included-image-wrapper img {
        width: 100%;
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

    /* Hộp "Lưu ý" */
    .not-included {
        margin-top: 40px;
        background-color: var(--background-pastel-light);
        border: 1px solid #cce0cc;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
    }
    .not-included h4 {
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    /* --- 4. Bảng giá --- */
    .pricing-intro {
        text-align: center;
        max-width: 650px;
        margin: 0 auto 40px auto;
    }
    .pricing-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
    }
    .pricing-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 30px 24px;
        text-align: center;
        background-color: var(--background-white);
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
        cursor: pointer;
    }
    .pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.1);
    }
    .pricing-card.selected {
        border-color: var(--primary-color);
        border-width: 2px;
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.15);
    }
    .pricing-card h3 {
        font-size: 1.4rem;
        color: var(--primary-color);
    }
    .price {
        font-size: 2.2rem;
        font-weight: bold;
        color: var(--text-dark);
        margin: 15px 0 5px 0;
    }
    .price span {
        font-size: 1rem;
        font-weight: normal;
        color: var(--text-secondary);
    }
    .discount-badge {
        position: absolute;
        top: 18px;
        right: 18px;
        background-color: var(--primary-color);
        color: var(--text-light);
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .pricing-card p {
        margin-top: 10px;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }
    .pricing-card ul {
        list-style: none;
        padding: 0;
        margin: 10px 0 0 0;
        text-align: left;
    }
    .pricing-card li {
        margin-bottom: 8px;
        padding-left: 25px;
        position: relative;
        font-size: 0.95rem;
    }
    .pricing-card li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--primary-color);
        font-weight: bold;
    }

    /* --- Responsive --- */
    @media (max-width: 1200px) {
        .pricing-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 992px) {
        h2 { font-size: 2.2rem; }
        .hero-section { flex-direction: column; text-align: center; }
        .hero-content h1 { font-size: 2.8rem; }
        .who-for .container { flex-direction: column; }
        .who-content h2 { text-align: center; }
        .included-row { flex-direction: column !important; gap: 30px; margin-bottom: 40px; }
        .included-content h3 { text-align: center; }
        .pricing-card { max-width: 420px; margin: 0 auto; }
    }
    @media (max-width: 768px) {
        h2 { font-size: 2rem; }
        .hero-section { padding-top: 20px; }
        .hero-content h1 { font-size: 2.5rem; }
        .hero-buttons { display: flex; flex-direction: column; gap: 15px; }
        .pricing-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricingCards = document.querySelectorAll('.pricing-card');

    pricingCards.forEach(card => {
        card.addEventListener('click', function () {
            // Bỏ class 'selected' khỏi tất cả các card
            pricingCards.forEach(c => c.classList.remove('selected'));

            // Thêm class 'selected' vào card được click
            card.classList.add('selected');

            const packageName = card.querySelector('h3').textContent;
            console.log('Đã chọn gói:', packageName);
            // Có thể bổ sung logic mở form / scroll đến form tư vấn ở đây
        });
    });
});
</script>
@endpush

@section('content')
    {{-- Hero --}}
    <section class="hero-section container">
        <div class="hero-content">
            <h1>Dịch vụ Giúp việc theo tháng</h1>
            <p class="tagline">
                An tâm trọn vẹn với nhân viên ổn định, gắn bó lâu dài. 
                Giải pháp chăm sóc gia đình toàn diện và tiết kiệm chi phí.
            </p>
            <div class="hero-buttons">
                <a href="#pricing" class="btn btn-primary">Xem gói ưu đãi theo tháng</a>
                <a href="/contact" class="btn btn-secondary">Đăng ký tư vấn</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="{{ asset('assets/giupviecthang-hero.jpg') }}" alt="Nhân viên giúp việc theo tháng đáng tin cậy">
        </div>
    </section>

    {{-- Phù hợp với gia đình nào --}}
    <section class="who-for">
        <div class="container">
            <div class="who-image">
                <img src="{{ asset('assets/giupviecthang-who-for.webp') }}" alt="Gia đình hạnh phúc có con nhỏ">
            </div>
            <div class="who-content">
                <h2>Phù hợp với gia đình nào?</h2>
                <ul class="who-list">
                    <li><span class="icon">✓</span>Gia đình có con nhỏ, bận rộn cần người hỗ trợ đều đặn hằng ngày.</li>
                    <li><span class="icon">✓</span>Mong muốn có nhân viên quen thuộc, tin cậy, hiểu rõ thói quen gia đình.</li>
                    <li><span class="icon">✓</span>Cần người phụ giúp các công việc hàng ngày (nấu ăn, dọn dẹp, giặt ủi cơ bản...).</li>
                    <li><span class="icon">✓</span>Chủ nhà muốn có sự ổn định, không phải lo tìm người mới liên tục.</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Dịch vụ bao gồm những gì --}}
    <section class="included container">
        <h2>Dịch vụ bao gồm những gì?</h2>

        <div class="included-row">
            <div class="included-content">
                <h3>Phòng khách / Phòng ngủ</h3>
                <ul>
                    <li>Quét, hút bụi và lau sàn nhà.</li>
                    <li>Lau bụi bẩn trên các bề mặt (bàn, ghế, tủ, kệ).</li>
                    <li>Vệ sinh bên ngoài các thiết bị (TV, quạt, máy lạnh).</li>
                    <li>Sắp xếp gối, chăn, ga trải giường gọn gàng.</li>
                    <li>Thu dọn và phân loại rác sinh hoạt.</li>
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
                    <li>Rửa sạch chén bát, dụng cụ nấu ăn của bữa gần nhất.</li>
                    <li>Lau sạch bề mặt bếp, bồn rửa, kệ bếp.</li>
                    <li>Vệ sinh bên ngoài các thiết bị (tủ lạnh, lò vi sóng, bếp...).</li>
                    <li>Lau bàn ăn và sắp xếp gọn gàng.</li>
                    <li>Thu dọn rác, thay túi rác khi đầy.</li>
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
                    <li>Cọ rửa bồn cầu, bồn rửa mặt, khu vực tắm.</li>
                    <li>Lau chùi gương và các bề mặt kính thấp.</li>
                    <li>Lau sàn nhà vệ sinh, giữ khô ráo, sạch sẽ.</li>
                    <li>Thu gom rác trong nhà vệ sinh.</li>
                </ul>
            </div>
            <div class="included-image-wrapper">
                <img src="{{ asset('assets/included-nhavesinh.jpg') }}" alt="Nhà vệ sinh sáng bóng">
            </div>
        </div>

        <div class="not-included">
            <h4>Lưu ý: Dịch vụ KHÔNG bao gồm:</h4>
            <p>
                Giặt ủi, phơi, gấp quần áo số lượng lớn; nấu ăn chuyên biệt nhiều món; 
                vệ sinh bên trong các thiết bị (tủ lạnh, lò nướng...); lau kính trên cao; 
                di chuyển đồ đạc nặng; vệ sinh sau xây dựng, cải tạo.
            </p>
        </div>
    </section>

    {{-- Bảng giá theo thời hạn hợp đồng --}}
    <section id="pricing" class="pricing container">
        <h2>Các gói dịch vụ theo tháng</h2>
        <p class="pricing-intro">
            Dịch vụ được tính theo thời hạn hợp đồng càng dài, ưu đãi càng lớn. 
            Mức giảm giá được áp dụng trực tiếp trên tổng phí dịch vụ trong kỳ đầu tiên của hợp đồng.
            Vui lòng liên hệ để nhận báo giá chi tiết và chính xác theo nhu cầu của gia đình.
        </p>

        <div class="pricing-grid">

    {{-- Gói 1 tháng --}}
    <div class="pricing-card">
        <span class="discount-badge">Giảm 5%</span>
        <h3>Gói 1 tháng</h3>
        <p>
            Phù hợp gia đình muốn trải nghiệm dịch vụ hoặc cần hỗ trợ trong thời gian ngắn.
        </p>
        <ul>
            <li>Hợp đồng 1 tháng, linh hoạt gia hạn.</li>
            <li>Nhân viên cố định trong suốt thời gian sử dụng.</li>
            <li>Ưu đãi giảm <strong>5%</strong> trên tổng phí dịch vụ tháng đầu.</li>
        </ul>
    </div>

    {{-- Gói 2 tháng --}}
    <div class="pricing-card">
        <span class="discount-badge">Giảm 10%</span>
        <h3>Gói 2 tháng</h3>
        <p>
            Lựa chọn tối ưu cho gia đình muốn ổn định nhân sự trong thời gian trung hạn.
        </p>
        <ul>
            <li>Hợp đồng 2 tháng, có thể tiếp tục gia hạn.</li>
            <li>Nhân viên gắn bó, quen nếp sinh hoạt gia đình.</li>
            <li>Ưu đãi giảm <strong>10%</strong> trên tổng phí dịch vụ.</li>
        </ul>
    </div>

    {{-- Gói 3 tháng --}}
    <div class="pricing-card">
        <span class="discount-badge">Giảm 15%</span>
        <h3>Gói 3 tháng</h3>
        <p>
            Gói được nhiều gia đình lựa chọn với mức tiết kiệm rõ rệt và tính ổn định cao.
        </p>
        <ul>
            <li>Hợp đồng 3 tháng, ưu tiên bố trí nhân sự lâu dài.</li>
            <li>Theo dõi chất lượng định kỳ và hỗ trợ thay thế khi cần.</li>
            <li>Ưu đãi giảm <strong>15%</strong> trên tổng phí dịch vụ.</li>
        </ul>
    </div>

    {{-- Gói 6 tháng --}}
    <div class="pricing-card">
        <span class="discount-badge">Giảm 20%</span>
        <h3>Gói 6 tháng</h3>
        <p>
            Giải pháp dài hạn, tối ưu chi phí cho gia đình cần người hỗ trợ ổn định lâu dài.
        </p>
        <ul>
            <li>Hợp đồng 6 tháng, cam kết nhân sự ổn định.</li>
            <li>Được ưu tiên hỗ trợ trong mọi phát sinh về nhân sự.</li>
            <li>Ưu đãi giảm <strong>20%</strong> trên tổng phí dịch vụ.</li>
        </ul>
    </div>

</div>

        </div>
    </section>

    {{-- Anchor để nút "Đăng ký tư vấn" kéo tới (có thể thay bằng form thật) --}}
    <section id="contact" class="container" style="padding-top: 0; padding-bottom: 60px; text-align:center;">
        <p>Nếu bạn quan tâm đến dịch vụ, vui lòng liên hệ hotline hoặc để lại thông tin để được tư vấn chi tiết.</p>
        {{-- Form/contact thực tế sẽ được đặt ở đây --}}
    </section>
@endsection
