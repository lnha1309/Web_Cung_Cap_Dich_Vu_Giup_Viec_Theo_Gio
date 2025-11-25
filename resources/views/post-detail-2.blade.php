@extends('layouts.base')
@section('title', 'bTaskee ký kết hợp tác đào tạo - bTaskee')
@push('styles')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #ffffff;
        color: #333;
        line-height: 1.8;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .breadcrumb {
        font-size: 14px;
        color: #999;
        margin-bottom: 20px;
    }

    .breadcrumb a {
        color: #999;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        color: #004d2e;
    }

    h1 {
        font-size: 40px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .meta-info {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        font-size: 14px;
        color: #666;
    }

    .meta-info a {
        color: #666;
        text-decoration: none;
    }

    .meta-info a:hover {
        color: #004d2e;
    }

    .featured-image {
        width: 100%;
        border-radius: 8px;
        margin-bottom: 40px;
    }

    .share-section {
        position: sticky;
        top: 100px;
        float: left;
        margin-left: -80px;
        margin-top: 20px;
    }

    .share-title {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .share-buttons {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .share-button {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .share-button:hover {
        background-color: #004d2e;
        transform: scale(1.1);
    }

    .share-button svg {
        width: 18px;
        height: 18px;
        fill: #666;
    }

    .share-button:hover svg {
        fill: #ffffff;
    }

    .article-content {
        font-size: 16px;
        color: #333;
    }

    .article-content p {
        margin-bottom: 20px;
        text-align: justify;
    }

    .article-content h2 {
        font-size: 24px;
        font-weight: bold;
        color: #1a1a1a;
        margin-top: 35px;
        margin-bottom: 20px;
    }

    .article-content h3 {
        font-size: 20px;
        font-weight: bold;
        color: #1a1a1a;
        margin-top: 30px;
        margin-bottom: 15px;
    }

    .article-content strong {
        font-weight: 600;
        color: #1a1a1a;
    }

    .article-content a {
        color: #004d2e;
        text-decoration: underline;
    }

    .article-content a:hover {
        color: #006640;
    }

    .highlight-date {
        font-style: italic;
        color: #666;
        margin-bottom: 25px;
        display: block;
    }

    /* Related Posts Section */
    .related-posts-section {
        background-color: #f5f5f5;
        padding: 60px 20px;
        margin-top: 60px;
    }

    .related-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .related-title {
        font-size: 32px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 40px;
    }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
    }

    .related-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .related-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    .related-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .related-content {
        padding: 25px;
    }

    .related-post-title {
        font-size: 18px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 15px;
        line-height: 1.4;
        min-height: 75px;
    }

    .related-meta {
        font-size: 14px;
        color: #888;
    }

    @media (max-width: 1024px) {
        .share-section {
            position: static;
            float: none;
            margin-left: 0;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .share-buttons {
            flex-direction: row;
        }
    }

    @media (max-width: 768px) {
        h1 {
            font-size: 28px;
        }

        .article-content h2 {
            font-size: 22px;
        }

        .article-content h3 {
            font-size: 18px;
        }

        .related-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .related-title {
            font-size: 28px;
            margin-bottom: 30px;
        }

        .related-posts-section {
            padding: 40px 20px;
            margin-top: 40px;
        }
    }
</style>
@endpush
@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ url('post') }}">Thông cáo báo chí</a>
    </div>

    <!-- Title -->
    <h1>bTaskee ký kết hợp tác đào tạo và tuyển dụng với Trường Cao đẳng Văn Lang Sài Gòn</h1>

    <!-- Meta Info -->
    <div class="meta-info">
        <span>Tháng Sáu 14, 2025</span>
        <a href="#">bTaskee Cleaning House</a>
    </div>

    <!-- Featured Image -->
    <img src="assets/baiBao3.jpg" alt="Lễ ký kết MOU" class="featured-image">

    <!-- Share Section -->
    <div class="share-section">
        <div class="share-title">Share</div>
        <div class="share-buttons">
            <button class="share-button" onclick="shareOn('facebook')" title="Share on Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                </svg>
            </button>
            <button class="share-button" onclick="shareOn('twitter')" title="Share on Twitter">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                </svg>
            </button>
            <button class="share-button" onclick="shareOn('pinterest')" title="Share on Pinterest">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 0a12 12 0 00-4.37 23.17c-.05-.95-.1-2.4.02-3.43l.74-3.14s-.19-.38-.19-.94c0-.88.51-1.54 1.15-1.54.54 0 .8.41.8.89 0 .54-.35 1.35-.53 2.1-.15.63.32 1.15.95 1.15 1.14 0 2.02-1.2 2.02-2.94 0-1.54-1.1-2.61-2.68-2.61-1.83 0-2.9 1.37-2.9 2.79 0 .55.21 1.14.48 1.46.05.06.06.12.04.18l-.18.73c-.03.11-.1.14-.22.08-.8-.37-1.3-1.53-1.3-2.46 0-2.01 1.46-3.86 4.21-3.86 2.21 0 3.93 1.58 3.93 3.68 0 2.19-1.38 3.96-3.3 3.96-.64 0-1.25-.33-1.46-.73l-.4 1.51c-.14.55-.53 1.24-.79 1.66A12 12 0 1012 0z" />
                </svg>
            </button>
            <button class="share-button" onclick="shareOn('linkedin')" title="Share on LinkedIn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                </svg>
            </button>
            <button class="share-button" onclick="shareOn('email')" title="Share via Email">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Article Content -->
    <div class="article-content">
        <span class="highlight-date">TP. Hồ Chí Minh, ngày 14 tháng 6 năm 2025</span>

        <p>Sáng ngày 14/6/2025, trong khuôn khổ chuỗi sự kiện VLSC Career 2025 và hội thảo khoa học với chủ đề "Mô hình doanh nghiệp trong trường và giải pháp nâng cao chất lượng đào tạo thực hành", Công ty TNHH bTaskee đã chính thức ký kết biên bản ghi nhớ hợp tác (MOU) với Trường Cao đẳng Văn Lang Sài Gòn. Lễ ký kết được tổ chức tại Trường Cao đẳng Văn Lang Sài Gòn, với sự tham dự của lãnh đạo hai bên, chuyên gia đào tạo, các giảng viên và đông đảo sinh viên ngành chăm sóc sắc đẹp.</p>

        <p>Biên bản ghi nhớ hợp tác này đánh dấu bước phát triển quan trọng trong quan hệ giữa doanh nghiệp và nhà trường, hướng tới việc tăng cường sự kết nối giữa lý thuyết và thực tiễn, đồng thời tạo điều kiện để sinh viên tiếp cận các cơ hội nghề nghiệp chất lượng ngay từ khi còn trên ghế nhà trường.</p>

        <p>Theo đó, <a href="https://www.btaskee.com/" target="_blank">bTaskee</a> – ứng dụng tiện ích gia đình hàng đầu Đông Nam Á, đồng thời đang phát triển dự án bBeauty cung cấp các dịch vụ làm đẹp tại nhà như gội đầu, làm nail, makeup… nhằm mở rộng hệ sinh thái chất lượng cao đến khách hàng, sẽ phối hợp cùng Trường Cao đẳng Văn Lang Sài Gòn triển khai các hoạt động tuyển dụng, thực tập và đào tạo thực tế dành cho sinh viên, đặc biệt là sinh viên ngành chăm sóc sắc đẹp.</p>

        <p>Thông qua hợp tác, bTaskee sẽ trực tiếp tuyển chọn và đào tạo sinh viên đáp ứng tiêu chuẩn của dự án làm đẹp bBeauty, đồng thời tạo điều kiện để các bạn trẻ trải nghiệm công việc thực tế, phát triển kỹ năng chuyên môn và nhận thù lao xứng đáng.</p>

        <p>Nhà trường sẽ hỗ trợ truyền thông về chương trình tuyển dụng, tạo điều kiện tổ chức kiểm tra tay nghề, phỏng vấn tại trường, đồng thời kết nối, giới thiệu những sinh viên tiềm năng đến với các cơ hội nghề nghiệp tại bTaskee. Đây là mô hình hợp tác giữa doanh nghiệp và cơ sở giáo dục nghề nghiệp nhằm góp phần nâng cao chất lượng đào tạo thực hành, đáp ứng tốt hơn nhu cầu của thị trường lao động hiện đại.</p>

        <p>Dự kiến, trong năm đầu tiên, chương trình hợp tác sẽ tiếp cận khoảng 20 – 30 sinh viên ngành chăm sóc sắc đẹp và tuyển chọn tối thiểu 10 ứng viên phù hợp tham gia dự án bBeauty của bTaskee. Hai bên cam kết phối hợp chặt chẽ trong suốt thời hạn hợp tác, thường xuyên tổ chức các hoạt động hội thảo nghề nghiệp, tọa đàm định hướng, thực tập và các sự kiện đào tạo thực tế, qua đó góp phần xây dựng hệ sinh thái nhân lực chất lượng cao cho ngành làm đẹp tại Việt Nam.</p>

        <p>Thông tin chi tiết về chương trình hợp tác và các hoạt động tuyển dụng sẽ được cập nhật trên website chính thức của bTaskee và Trường Cao đẳng Văn Lang Sài Gòn.</p>
    </div>
</div>

<!-- Related Posts Section -->
<div class="related-posts-section">
    <div class="related-container">
        <h2 class="related-title">Bài viết liên quan</h2>

        <div class="related-grid">
            <!-- Related Post 1 -->
            <div class="related-card" onclick=window.location.href="{{ url('post-detail-1') }}">
                <img src="assets/baiBao1.jpg" alt="bTaskee ra mắt bBeauty" class="related-image">
                <div class="related-content">
                    <h3 class="related-post-title">bTaskee ra mắt bBeauty, đánh dấu bước tiến mới trong hệ sinh thái dịch vụ gia đình</h3>
                    <p class="related-meta">bTaskee Cleaning House • 25/06/2025</p>
                </div>
            </div>

            <!-- Related Post 2 -->
            <div class="related-card" onclick=window.location.href="{{ url('post-detail-3') }}">
                <img src="assets/baiBao2.jpg" alt="Vệ sinh công nghiệp" class="related-image">
                <div class="related-content">
                    <h3 class="related-post-title">bTaskee Chính Thức Ra Mắt Dịch Vụ Vệ Sinh Công Nghiệp</h3>
                    <p class="related-meta">bTaskee Cleaning House • 13/01/2025</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function shareOn(platform) {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);

        let shareUrl = '';

        switch (platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'pinterest':
                shareUrl = `https://pinterest.com/pin/create/button/?url=${url}&description=${title}`;
                break;
            case 'linkedin':
                shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${url}&title=${title}`;
                break;
            case 'email':
                shareUrl = `mailto:?subject=${title}&body=${url}`;
                break;
        }

        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }

    // Animation effects
    document.addEventListener('DOMContentLoaded', function() {
        const relatedCards = document.querySelectorAll('.related-card');

        const relatedObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        }, {
            threshold: 0.1
        });

        relatedCards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            relatedObserver.observe(card);
        });
    });
</script>
@endsection