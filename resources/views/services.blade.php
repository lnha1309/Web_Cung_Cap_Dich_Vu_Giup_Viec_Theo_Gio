@extends('layouts.base')
@section('title', 'Dịch vụ')
@section('content')

<style>
    :root {
        --brand-dark: #0b5233;
        --brand-light: #e8f4ed;
        --text-main: #10221b;
        --text-muted: #6b7a72;
        --border-soft: #e3ebe6;
        --bg-page: #f5f7f6;
    }

    .services-page {
        background: var(--bg-page);
    }

    /* ===== HERO ===== */
    .services-hero {
        position: relative;
        padding: 72px 16px 40px;
        text-align: center;
        overflow: hidden;
        background: linear-gradient(135deg, #f7fbf9 0%, #ffffff 60%, #f2faf6 100%);
    }

    .services-hero::before,
    .services-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        filter: blur(40px);
        opacity: 0.35;
        pointer-events: none;
    }

    .services-hero::before {
        width: 260px;
        height: 260px;
        background: rgba(11, 82, 51, 0.12);
        top: -60px;
        left: -60px;
    }

    .services-hero::after {
        width: 260px;
        height: 260px;
        background: rgba(11, 82, 51, 0.16);
        bottom: -80px;
        right: -40px;
    }

    .services-hero-inner {
        max-width: 960px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .services-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(11, 82, 51, 0.06);
        color: var(--brand-dark);
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 14px;
    }

    .services-kicker i {
        font-size: 14px;
    }

    .services-hero h1 {
        font-size: 38px;
        font-weight: 800;
        letter-spacing: -0.04em;
        color: var(--text-main);
        margin-bottom: 12px;
    }

    .services-hero p {
        font-size: 16px;
        color: var(--text-muted);
        max-width: 620px;
        margin: 0 auto 18px;
        line-height: 1.7;
    }

    .services-hero-meta {
        display: flex;
        justify-content: center;
        gap: 18px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #526058;
        margin-top: 8px;
    }

    .services-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 11px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #dde7e1;
    }

    .services-hero-meta i {
        color: var(--brand-dark);
    }

    /* ===== WRAPPER ===== */
    .services-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 16px 80px;
    }

    /* ===== GRID ===== */
    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    }

    /* ===== CARD ===== */
    .service-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid var(--border-soft);
        padding: 22px 20px 20px;
        box-shadow: 0 10px 25px rgba(15, 41, 30, 0.04);
        display: flex;
        flex-direction: column;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        position: relative;
        overflow: hidden;

        /* QUAN TRỌNG: ép rộng + reset vertical text từ CSS cũ */
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        writing-mode: horizontal-tb;
    }

    /* reset luôn cho mọi phần tử bên trong card phòng trường hợp CSS global set vertical writing */
    .service-card * {
        writing-mode: horizontal-tb !important;
        white-space: normal;
    }

    .service-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top left, rgba(11, 82, 51, 0.06), transparent 55%);
        opacity: 0;
        transition: opacity 0.25s ease;
        pointer-events: none;
    }

    .service-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 40px rgba(15, 41, 30, 0.08);
        border-color: rgba(11, 82, 51, 0.35);
    }

    .service-card:hover::before {
        opacity: 1;
    }

    .service-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .service-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1.35;
    margin: 0;
    word-break: break-word;
    min-height: 52px; /* khoảng cho ~2 dòng chữ */
}


    .service-pill {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        background: var(--brand-light);
        color: var(--brand-dark);
        border: 1px solid #d3e7da;
        white-space: nowrap;
    }

    .service-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 4px 0 10px;
    min-height: 48px; 
}


    .service-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f6faf8;
        border: 1px solid #dde7e1;
        font-size: 13px;
        font-weight: 600;
        color: #324238;
    }

    .service-meta-item i {
        font-size: 13px;
        color: var(--brand-dark);
    }

    .service-divider {
        height: 1px;
        background: #edf2ef;
        margin: 10px 0 14px;
    }

    .service-price-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
    }

    .service-price {
        font-size: 24px;
        font-weight: 900;
        color: var(--brand-dark);
        letter-spacing: -0.03em;
    }

    .service-price-note {
        font-size: 12px;
        color: var(--text-muted);
    }

    .service-desc {
        font-size: 14px;
        color: var(--text-muted);
        line-height: 1.7;
        margin-bottom: 14px;
        min-height: 54px;
    }

    .service-actions {
        display: flex;
        gap: 12px;
        width: 100%;
    }

    .service-actions .action-slot {
        flex: 1 1 0;
        min-width: 0;
    }

    .service-actions .action-slot > * {
        width: 100%;
        text-decoration: none;
        display: block;
    }

    .btn-solid,
    .btn-outline {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 52px;
        border-radius: 20px;
        font-size: 17px;
        font-weight: 700;
        border: 2px solid #0b5233;
        cursor: pointer;
        white-space: nowrap;
        padding: 0;
    }



/* Nút đặc */
.btn-solid {
    background: linear-gradient(135deg, #0b5233, #0e6b40);
    color: #fff;
}
.btn-solid:hover {
    background: linear-gradient(135deg, #094229, #0b5233);
    transform: translateY(-2px);
}

/* Nút viền */
.btn-outline {
    background: #fff;
    color: #0b5233;
    border-color: #bfd2c7;   /* hợp tone hơn */
}
.btn-outline:hover {
    background: #f6faf8;
    transform: translateY(-2px);
}

    .btn-outline {
        background: #ffffff;
        color: var(--brand-dark);
        border-color: rgba(11, 82, 51, 0.25);
    }

    .btn-outline:hover {
        background: #f6faf8;
        border-color: rgba(11, 82, 51, 0.6);
        transform: translateY(-1px);
    }

    /* ===== EMPTY ===== */
    .empty-state {
        text-align: center;
        padding: 60px 16px 80px;
        color: var(--text-muted);
        font-size: 15px;
    }

    .empty-state-icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        background: #ffffff;
        border: 1px dashed #c6d5cc;
        color: var(--brand-dark);
        font-size: 22px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .services-hero {
            padding: 56px 16px 32px;
        }

        .services-hero h1 {
            font-size: 30px;
        }

        .services-hero p {
            font-size: 15px;
        }

        .services-wrapper {
            padding: 32px 14px 64px;
        }

        .services-grid {
            gap: 18px;
        }

        .service-card {
            padding: 18px 16px 16px;
        }

        .service-title {
            font-size: 18px;
        }

        .service-price {
            font-size: 22px;
        }
    }
</style>

<div class="services-page">
    {{-- HERO --}}
    <section class="services-hero">
        <div class="services-hero-inner">
            <div class="services-kicker">
                <i class="fa-solid fa-sparkles"></i>
                <span>Dịch vụ bTaskee</span>
            </div>
            <h1>Chọn gói dịch vụ phù hợp với gia đình bạn</h1>
            <p>
                Bạn có thể dễ dàng so sánh và đặt lịch chỉ trong vài bước.
            </p>

            <div class="services-hero-meta">
                <span><i class="fa-solid fa-shield-heart"></i> Nhân viên đã được kiểm tra lý lịch</span>
                <span><i class="fa-solid fa-bolt"></i> Đặt lịch nhanh trong vài phút</span>
                <span><i class="fa-solid fa-star"></i> Hàng nghìn khách hàng tin dùng</span>
            </div>
        </div>
    </section>

    {{-- LIST --}}
    <div class="services-wrapper">
        @if($services->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fa-regular fa-face-smile-wink"></i>
                </div>
                <p>Hiện chưa có dịch vụ nào được cấu hình. Vui lòng quay lại sau bạn nhé.</p>
            </div>
        @else
            <div class="services-grid">
                @foreach($services as $service)
                    @php
                        $hours = $service->ThoiLuong !== null ? (float) $service->ThoiLuong : null;
                        $hoursLabel = $hours !== null
                            ? (fmod($hours, 1) === 0.0
                                ? (int) $hours
                                : rtrim(rtrim(number_format($hours, 1, '.', ''), '0'), '.')
                              ) . ' giờ'
                            : 'Theo nhu cầu';

                        $area = $service->DienTichToiDa !== null
                            ? rtrim(rtrim(number_format((float) $service->DienTichToiDa, 0, ',', '.'), '0'), '.') . ' m²'
                            : null;

                        $rooms = $service->SoPhong ? $service->SoPhong . ' phòng' : null;

                        $price = $service->GiaDV !== null
                            ? number_format((float) $service->GiaDV, 0, ',', '.') . ' đ'
                            : 'Liên hệ';
                    @endphp

                    <article class="service-card">
                        <div class="service-top">
                            <h2 class="service-title">{{ $service->TenDV }}</h2>
                            <span class="service-pill">
                                <i class="fa-regular fa-clock"></i> {{ $hoursLabel }}
                            </span>
                        </div>

                        <div class="service-price-row">
                            <div class="service-price">{{ $price }}</div>
                           
                        </div>

                        <p class="service-desc">
                            {{ $service->MoTa ?: 'Dịch vụ tiêu chuẩn, phù hợp với nhu cầu sinh hoạt phổ biến của gia đình.' }}
                        </p>

                        @php
                            $isComingSoon = false;
                            if (!empty($service->ID_DV) && preg_match('/^DV(\\d+)/', $service->ID_DV, $m)) {
                                $isComingSoon = (int) $m[1] >= 4;
                            }
                        @endphp
                        <div class="service-actions">
                            <div class="action-slot">
                                <a href="{{ url('select-address') }}">
                                    <button type="button" class="btn-solid">
                                        Đặt ngay
                                    </button>
                                </a>
                            </div>
                            <div class="action-slot">
                                @if($isComingSoon)
                                    <button
                                        type="button"
                                        class="btn-outline"
                                        onclick="alert('Coming soon - Trang giới thiệu dịch vụ đang được xây dựng')"
                                    >
                                        Xem chi tiết
                                    </button>
                                @else
                                    <a href="{{ url('giupviectheogio') }}">
                                        <button type="button" class="btn-outline">
                                            Xem chi tiết
                                        </button>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
