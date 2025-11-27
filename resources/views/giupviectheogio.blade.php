@extends('layouts.base')
@section('title', 'Dịch vụ giúp việc theo giờ')

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

    .hourly-container {
        width: 90%;
        max-width: 1100px;
        margin: 0 auto;
    }

    .hourly-section {
        padding: 70px 0;
    }

    .hourly-section h2 {
        font-size: 2.5rem;
        text-align: center;
        margin-bottom: 50px;
        color: var(--primary-color);
    }

    .hourly-section img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
    }

    /* Nút bấm chung */
    .hourly-btn {
        display: inline-block;
        padding: 12px 28px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .hourly-btn-primary {
        background-color: var(--primary-color);
        color: var(--text-light);
        border: 2px solid var(--primary-color);
    }

    .hourly-btn-primary:hover {
        background-color: var(--primary-color-dark);
        border-color: var(--primary-color-dark);
    }

    .hourly-btn-secondary {
        background-color: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
    }

    .hourly-btn-secondary:hover {
        background-color: var(--primary-color);
        color: var(--text-light);
    }

    /* --- 1. Phần Hero (Split Layout) --- */
    .hourly-hero-section {
        padding-top: 50px;
        display: flex;
        align-items: center;
        gap: 40px;
    }

    .hourly-hero-content {
        flex: 1;
    }

    .hourly-hero-content h1 {
        font-size: 3.2rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .hourly-hero-content .tagline {
        font-size: 1.2rem;
        margin-bottom: 30px;
    }

    .hourly-hero-buttons .hourly-btn {
        margin-right: 15px;
    }

    .hourly-hero-image {
        flex: 1;
    }

    .hourly-hero-image img {
        box-shadow: 0 10px 30px rgba(0, 77, 46, 0.1);
    }

    /* --- 2. Dịch vụ này dành cho ai? --- */
    .hourly-who-for {
        background-color: var(--background-gray-light);
    }

    .hourly-who-for .hourly-container {
        display: flex;
        align-items: center;
        gap: 50px;
    }

    .hourly-who-image {
        flex: 1;
    }

    .hourly-who-content {
        flex: 1;
    }

    .hourly-who-content h2 {
        text-align: left;
    }

    .hourly-who-list {
        list-style: none;
        padding-left: 0;
    }

    .hourly-who-list li {
        font-size: 1.1rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .hourly-who-list .icon {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-right: 15px;
    }

    /* --- 3. Danh Sách Công Việc (Layout xen kẽ) --- */
    .hourly-included-row {
        display: flex;
        align-items: center;
        gap: 50px;
        margin-bottom: 60px;
    }

    .hourly-included-row.row-reverse {
        flex-direction: row-reverse;
    }

    .hourly-included-content {
        flex: 1;
    }

    .hourly-included-image-wrapper img {
        width: 600px;
        height: 350px;
        object-fit: cover;
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.1);
        border-radius: 12px;
    }

    .hourly-included-content h3 {
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    .hourly-included-content ul {
        list-style: none;
        padding-left: 0;
    }

    .hourly-included-content li {
        position: relative;
        padding-left: 30px;
        margin-bottom: 12px;
        font-size: 1.05rem;
    }

    .hourly-included-content li::before {
        content: '✓';
        position: absolute;
        left: 0;
        top: 0;
        color: var(--primary-color);
        font-weight: bold;
        font-size: 1.2rem;
    }

    .hourly-not-included {
        margin-top: 40px;
        background-color: #fff8f8;
        border: 1px solid #ffe0e0;
        border-radius: 8px;
        padding: 25px;
        text-align: center;
    }

    .hourly-not-included h4 {
        color: #d9534f;
        margin-bottom: 10px;
    }

    @media (max-width: 992px) {
        .hourly-included-row {
            flex-direction: column !important;
            gap: 30px;
            margin-bottom: 40px;
        }

        .hourly-included-content h3 {
            text-align: center;
        }
    }

    /* --- 4. Quy trình đặt dịch vụ --- */
    .hourly-how-it-works {
        background-color: var(--background-pastel-light);
    }

    .hourly-steps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
        text-align: center;
    }

    .hourly-step img {
        width: 120px;
        height: 120px;
        margin-bottom: 20px;
    }

    .hourly-step h4 {
        font-size: 1.3rem;
        color: var(--primary-color);
    }

    /* --- 5. Bảng giá --- */
    .hourly-pricing-intro {
        text-align: center;
        max-width: 600px;
        margin: 0 auto 40px auto;
    }

    .hourly-pricing-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .hourly-pricing-card {
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
    .hourly-pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.1);
    }

    /* Class mới cho card được chọn */
    .hourly-pricing-card.selected {
        border-color: var(--primary-color);
        border-width: 2px;
        box-shadow: 0 8px 25px rgba(0, 77, 46, 0.15);
    }

    .hourly-pricing-card h3 {
        font-size: 1.2rem;
        color: var(--primary-color);
    }

    .hourly-price {
        font-size: 2.8rem;
        font-weight: bold;
        color: var(--text-dark);
        margin: 15px 0;
    }

    .hourly-price span {
        font-size: 1rem;
        font-weight: normal;
        color: var(--text-secondary);
    }

    .hourly-pricing-card ul {
        list-style: none;
        padding: 0;
        margin: 20px 0;
        text-align: left;
    }

    .hourly-pricing-card li {
        margin-bottom: 10px;
        padding-left: 25px;
        position: relative;
    }

    .hourly-pricing-card li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--primary-color);
    }
    
    .hourly-pricing-card .hourly-btn {
        margin-top: auto;
    }

    /* --- Responsive cho di động --- */
    @media (max-width: 992px) {
        .hourly-section h2 {
            font-size: 2.2rem;
        }

        .hourly-hero-section {
            flex-direction: column;
            text-align: center;
        }

        .hourly-hero-content h1 {
            font-size: 2.8rem;
        }

        .hourly-who-for .hourly-container {
            flex-direction: column;
        }

        .hourly-who-content h2 {
            text-align: center;
        }

        .hourly-pricing-grid {
            grid-template-columns: 1fr;
        }

        .hourly-pricing-card {
            max-width: 400px;
            margin: 0 auto;
        }
    }

    @media (max-width: 768px) {
        .hourly-container {
            width: calc(100% - 32px);
        }

        .hourly-section h2 {
            font-size: 2rem;
        }

        .hourly-hero-section {
            padding-top: 20px;
        }

        .hourly-hero-content h1 {
            font-size: 2.5rem;
        }

        .hourly-hero-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .hourly-steps-grid {
            grid-template-columns: 1fr;
        }

        /* Pricing Optimization */
        .hourly-pricing-grid {
            gap: 20px;
        }

        .hourly-pricing-card {
            padding: 30px 20px 40px 20px;
            width: 100%; /* Ensure full width within max-width */
        }

        .hourly-pricing-card p {
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3em; /* Ensure space for at least 2 lines (1.5 * 2) to equalise height */
        }

        .hourly-pricing-card li {
            padding-left: 32px;
            margin-bottom: 12px;
        }

        .hourly-pricing-card li::before {
            font-size: 20px;
            top: -2px;
        }

        .hourly-pricing-card .hourly-btn {
            min-height: 48px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pricingCards = document.querySelectorAll('.hourly-pricing-card');
    const selectAddressUrl = "{{ route('booking.selectAddress') }}";
    pricingCards.forEach(card => {
        const button = card.querySelector('.hourly-btn');
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
<section class="hourly-section hourly-hero-section hourly-container">
    <div class="hourly-hero-content">
        <h1>Dịch vụ Giúp việc theo giờ</h1>
        <p class="tagline">Linh hoạt, tin cậy, cho không gian sống luôn sạch tinh tươm. Bạn chọn giờ, chúng tôi lo việc nhà.
Đội ngũ chuyên nghiệp, được đào tạo bài bản, đảm bảo công việc được thực hiện nhanh chóng và chuẩn xác.
Giải pháp lý tưởng cho những gia đình bận rộn cần sự hỗ trợ định kỳ hoặc đột xuất.</p>
        <div class="hourly-hero-buttons">
            <a href="{{ url('select-address') }}" class="hourly-btn hourly-btn-secondary">Đặt lịch ngay</a>
            <a href="#pricing" class="hourly-btn hourly-btn-secondary">Xem bảng giá</a>
        </div>
    </div>
    <div class="hourly-hero-image">
        <img src="{{ asset('assets/hero-image.jpg') }}" alt="Nhân viên giúp việc theo giờ đang dọn dẹp">
    </div>
</section>

<section class="hourly-section hourly-who-for">
    <div class="hourly-container">
        <div class="hourly-who-image">
            <img src="{{ asset('assets/who-for.jfif') }}" alt="Người phụ nữ thư giãn trên sofa">
        </div>
        <div class="hourly-who-content">
            <h2>Dịch vụ này dành cho ai?</h2>
            <ul class="hourly-who-list">
                <li><span class="icon">✓</span>Người bận rộn với công việc, không có thời gian dọn dẹp.</li>
                <li><span class="icon">✓</span>Gia đình trẻ cần thêm thời gian chăm sóc con cái, nghỉ ngơi.</li>
                <li><span class="icon">✓</span>Người sống một mình muốn dọn dẹp căn hộ nhanh chóng.</li>
                <li><span class="icon">✓</span>Bất cứ ai muốn tận hưởng cuối tuần thảnh thơi mà nhà cửa vẫn sạch sẽ.</li>
            </ul>
        </div>
    </div>
</section>

<section class="hourly-section hourly-included hourly-container">
    <h2>Dịch vụ bao gồm những gì?</h2>

    <div class="hourly-included-row">
        <div class="hourly-included-content">
            <h3>Phòng khách / Phòng ngủ</h3>
            <ul>
                <li>Quét, hút bụi và lau sàn nhà.</li>
                <li>Lau bụi bẩn trên các bề mặt (bàn, ghế, tủ, kệ).</li>
                <li>Vệ sinh bên ngoài các thiết bị (TV, quạt, máy lạnh).</li>
                <li>Sắp xếp lại gối, chăn, ga trải giường (không thay).</li>
                <li>Thu dọn rác.</li>
            </ul>
        </div>
        <div class="hourly-included-image-wrapper">
            <img src="{{ asset('assets/included-phongkhach.webp') }}" alt="Phòng khách sạch sẽ">
        </div>
    </div>

    <div class="hourly-included-row row-reverse">
        <div class="hourly-included-content">
            <h3>Nhà bếp</h3>
            <ul>
                <li>Rửa sạch chén bát (cho 1 bữa ăn gần nhất).</li>
                <li>Lau sạch bề mặt bếp, bồn rửa.</li>
                <li>Vệ sinh bên ngoài các thiết bị (tủ lạnh, lò vi sóng, bếp).</li>
                <li>Lau bàn ăn.</li>
                <li>Thu dọn rác.</li>
            </ul>
        </div>
        <div class="hourly-included-image-wrapper">
            <img src="{{ asset('assets/included-nhabep.jpg') }}" alt="Nhà bếp gọn gàng">
        </div>
    </div>

    <div class="hourly-included-row">
        <div class="hourly-included-content">
            <h3>Nhà vệ sinh</h3>
            <ul>
                <li>Cọ rửa bồn cầu, bồn rửa mặt, vòi sen/bồn tắm.</li>
                <li>Lau chùi gương.</li>
                <li>Lau sàn nhà vệ sinh.</li>
            </ul>
        </div>
        <div class="hourly-included-image-wrapper">
            <img src="{{ asset('assets/included-nhavesinh.jpg') }}" alt="Nhà vệ sinh sáng bóng">
        </div>
    </div>

    <div class="hourly-not-included">
        <h4>Lưu ý: Dịch vụ KHÔNG bao gồm:</h4>
        <p>Giặt ủi, phơi, gấp quần áo; Nấu ăn; Vệ sinh bên trong các thiết bị (tủ lạnh...); Lau kính trên cao; Di chuyển đồ đạc nặng; Vệ sinh sau xây dựng.</p>
    </div>
</section>

<section class="hourly-section hourly-how-it-works">
    <div class="hourly-container">
        <h2>Chỉ với 3 bước đơn giản</h2>
        <div class="hourly-steps-grid">
            <div class="hourly-step">
                <img src="{{ asset('assets/step1.png') }}" alt="Biểu tượng lịch">
                <h4>1. Chọn lịch hẹn</h4>
                <p>Chọn ngày, giờ và số giờ bạn cần. Hệ thống luôn cập nhật lịch trống.</p>
            </div>
            <div class="hourly-step">
                <img src="{{ asset('assets/step2.png') }}" alt="Biểu tượng ngôi nhà">
                <h4>2. Nhập thông tin</h4>
                <p>Cung cấp địa chỉ của bạn và bất kỳ ghi chú đặc biệt nào cho nhân viên.</p>
            </div>
            <div class="hourly-step">
                <img src="{{ asset('assets/step3.png') }}" alt="Biểu tượng nhân viên dọn dẹp">
                <h4>3. Xác nhận & Thư giãn</h4>
                <p>Chúng tôi xác nhận lịch và nhân viên sẽ có mặt đúng hẹn. Tận hưởng nhà sạch!</p>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="hourly-section hourly-pricing hourly-container">
    <h2>Bảng giá dịch vụ</h2>
    <p class="hourly-pricing-intro">Chúng tôi cam kết một mức giá minh bạch, không phát sinh phụ phí. Bạn chỉ trả tiền cho thời gian bạn sử dụng.</p>
    @php
        $packagesByDuration = collect($hourlyPackages ?? [])->keyBy('duration');
        $packageContent = [
            2 => [
                'fallback_name' => 'Gói 2 giờ',
                'fallback_price' => 192000,
                'description' => 'Lý tưởng cho căn hộ studio hoặc 1 phòng ngủ.',
                'features' => [
                    'Dọn dẹp cơ bản',
                    'Tập trung 1-2 khu vực',
                ],
            ],
            3 => [
                'fallback_name' => 'Gói 3 giờ',
                'fallback_price' => 240000,
                'description' => 'Phổ biến nhất! Phù hợp cho nhà 2 phòng ngủ.',
                'features' => [
                    'Dọn dẹp toàn diện',
                    'Đủ thời gian cho các khu vực',
                ],
            ],
            4 => [
                'fallback_name' => 'Gói 4 giờ',
                'fallback_price' => 320000,
                'description' => 'Dành cho nhà lớn, hoặc cần dọn dẹp kỹ.',
                'features' => [
                    'Dọn dẹp sâu, chi tiết',
                    'Bao quát toàn bộ nhà',
                ],
            ],
        ];
    @endphp
    <div class="hourly-pricing-grid">
        @foreach($packageContent as $duration => $content)
            @php
                $data = $packagesByDuration->get($duration, []);
                $name = $data['name'] ?? $content['fallback_name'];
                $price = $data['price'] ?? $content['fallback_price'];
                $description = $data['description'] ?? $content['description'];
            @endphp
            <div class="hourly-pricing-card" data-duration="{{ $duration }}">
                <h3>{{ $name }}</h3>
                <div class="hourly-price">{{ number_format((float) $price, 0, ',', '.') }}đ</div>
                <p>{{ $description }}</p>
                <ul>
                    @foreach($content['features'] as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('booking.selectAddress', ['duration' => $duration]) }}" class="hourly-btn hourly-btn-secondary" data-duration="{{ $duration }}">Chọn gói này</a>
            </div>
        @endforeach
    </div>
</section>
@endsection
