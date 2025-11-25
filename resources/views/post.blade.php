@extends('layouts.base')
@section('title', 'Tin tức báo chí')
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
        overflow-x: hidden; /* Prevent horizontal scroll */
    }

    .container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px; /* Default padding */
    }

    h1 {
        font-size: 48px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 40px;
        margin-top: 20px;
    }

    .post-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
        margin-bottom: 40px;
    }

    .post-card {
        background: #fff;
        border-radius: 8px; /* Requirement: 8px */
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08); /* Requirement: specific shadow */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
        margin-bottom: 20px; /* Requirement: margin-bottom */
        display: flex;
        flex-direction: column;
    }

    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .post-image {
        width: 100%; /* Requirement: 100% width */
        height: auto; /* Requirement: auto height */
        aspect-ratio: 16 / 9; /* Requirement: 16:9 ratio */
        object-fit: cover; /* Requirement: cover */
        display: block;
    }

    .post-content {
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .post-title {
        font-size: 16px; /* Requirement: 15-16px */
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 10px;
        line-height: 1.4;
        
        /* Requirement: line-clamp 2 */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 44px; /* Approx 2 lines height to maintain alignment */
    }

    .post-date {
        font-size: 14px;
        color: #888;
        margin-top: auto; /* Push to bottom */
        padding-top: 10px;
    }

    @media (max-width: 1024px) {
        .post-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 16px; /* Requirement: 12-16px padding */
        }

        .post-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        h1 {
            font-size: 32px; /* Adjusted for mobile */
            margin-bottom: 30px;
        }

        .post-title {
            font-size: 16px;
        }
    }
</style>
@endpush
@section('content')
<div class="container">
        <h1>Thông Cáo Báo Chí</h1>
        
        <div class="post-grid">
            <!-- Post 1 -->
            <div class="post-card" onclick=window.location.href="{{ url('post-detail-1') }}">
                <img src="assets/baiBao1.jpg" alt="bTaskee ra mắt bBeauty" class="post-image">
                <div class="post-content">
                    <h2 class="post-title">bTaskee ra mắt bBeauty, đánh dấu bước tiến mới trong hệ sinh thái dịch vụ gia đình</h2>
                    <p class="post-date">25/06/2025</p>
                </div>
            </div>

            <!-- Post 2 -->
            <div class="post-card" onclick=window.location.href="{{ url('post-detail-2') }}">
                <img src="assets/baiBao3.jpg" alt="Lễ ký kết MOU" class="post-image">
                <div class="post-content">
                    <h2 class="post-title">bTaskee ký kết hợp tác đào tạo và tuyển dụng với Trường Cao đẳng Văn Lang Sài Gòn</h2>
                    <p class="post-date">14/06/2025</p>
                </div>
            </div>

            <!-- Post 3 -->
            <div class="post-card" onclick=window.location.href="{{ url('post-detail-3') }}">
                <img src="assets/baiBao2.jpg" alt="VSCN" class="post-image">
                <div class="post-content">
                    <h2 class="post-title">bTaskee Chính Thức Ra Mắt Dịch Vụ Vệ Sinh Công Nghiệp</h2>
                    <p class="post-date">13/01/2025</p>
                </div>
            </div>

            

    <script>
        // Thêm hiệu ứng khi scroll
        document.addEventListener('DOMContentLoaded', function() {
            const postCards = document.querySelectorAll('.post-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            postCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
@endsection