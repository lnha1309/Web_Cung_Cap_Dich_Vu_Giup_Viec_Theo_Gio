@extends('layouts.base')
@section('title', 'Thông Cáo Báo Chí - bTaskee')
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

    .container {
        max-width: 1100px;
        margin: 0 auto;
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
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .post-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
    }

    .post-content {
        padding: 25px;
    }

    .post-title {
        font-size: 20px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 15px;
        line-height: 1.4;
        min-height: 80px;
    }

    .post-date {
        font-size: 14px;
        color: #888;
        margin-top: 15px;
    }

    @media (max-width: 1024px) {
        .post-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .post-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        h1 {
            font-size: 36px;
        }

        .post-title {
            font-size: 20px;
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