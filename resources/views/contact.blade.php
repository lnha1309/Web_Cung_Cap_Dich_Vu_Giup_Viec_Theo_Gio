@extends('layouts.base')
@section('title', 'Liên hệ - BTaskee')

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

    .contact-page-container {
        max-width: 1200px;
        margin: 40px auto;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .contact-content {
        display: grid;
        grid-template-columns: 0.9fr 1.1fr;
        gap: 0;
        min-height: 600px;
    }

    .form-section {
        padding: 50px 40px;
        background: #fafafa;
    }

    .form-section h1 {
        font-size: 38px;
        font-weight: 700;
        margin-bottom: 35px;
        color: #1a1a1a;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: none;
        background: #e8e8e8;
        border-radius: 8px;
        font-size: 14px;
        color: #333;
        transition: background 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        background: #dedede;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
    }

    .submit-btn {
        width: 100%;
        max-width: 250px;
        padding: 14px 35px;
        background: #004d2e;
        border: none;
        border-radius: 30px;
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 10px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 77, 46, 0.4);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .contact-info-section {
        padding: 50px 40px;
        background: white;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .contact-info-section h2 {
        font-size: 38px;
        font-weight: 700;
        margin-bottom: 45px;
        color: #1a1a1a;
    }

    .contact-page-item {
        margin-bottom: 35px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .contact-page-item-icon {
        width: 30px;
        height: 30px;
        flex-shrink: 0;
    }

    .contact-page-item-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .contact-page-item-value {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        padding-top: 5px;
    }

    .illustration-image {
        position: absolute;
        bottom: 0;
        right: 0;
        max-width: 500px;
        width: 100%;
        height: auto;
    }

    .illustration-image img {
        width: 100%;
        height: auto;
        display: block;
    }

    /* Office Location Section - Redesigned */
    .office-section {
        max-width: 1200px;
        margin: 40px auto;
        padding: 60px 40px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .office-header-section {
        text-align: center;
        margin-bottom: 50px;
    }

    .office-section h2 {
        font-size: 38px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #1a1a1a;
    }

    .office-section .subtitle {
        font-size: 16px;
        color: #666;
        line-height: 1.6;
    }

    .city-title {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 12px 30px;
        background: #004d2e;
        color: white;
        font-size: 22px;
        font-weight: 700;
        border-radius: 50px;
        margin-bottom: 40px;
    }


    .office-info-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 40px;
        flex-wrap: wrap;
    }

    .office-info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        color: #555;
    }

    .office-info-item span {
        font-weight: 600;
        color: #1a1a1a;
    }

    .addresses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }

    .address-card {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        border: 2px solid #e9ecef;
        border-radius: 15px;
        padding: 25px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .address-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: #004d2e;
    }

    .address-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #004d2e;
    }

    .address-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        background: #004d2e;
        color: white;
        font-weight: 700;
        font-size: 16px;
        border-radius: 50%;
        margin-bottom: 15px;
    }

    .address-card p {
        font-size: 15px;
        color: #444;
        line-height: 1.7;
        margin: 0;
    }



    @media (max-width: 968px) {
        .contact-content {
            grid-template-columns: 1fr;
        }

        .illustration-image {
            position: relative;
            margin: 40px auto 0;
            max-width: 400px;
        }

        .form-section,
        .contact-info-section {
            padding: 40px 30px;
        }

        .form-section h1,
        .contact-info-section h2 {
            font-size: 32px;
        }

        .office-section {
            padding: 40px 30px;
            margin: 40px 20px;
        }

        .office-section h2 {
            font-size: 32px;
        }

        .addresses-grid {
            grid-template-columns: 1fr;
        }

        .city-title {
            font-size: 18px;
        }
    }

    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .form-section,
        .contact-info-section {
            padding: 30px 20px;
        }

        .illustration-image {
            max-width: 300px;
        }

        .office-section {
            padding: 30px 20px;
        }

        .office-section h2 {
            font-size: 28px;
        }

        .office-info-bar {
            flex-direction: column;
            gap: 15px;
        }
    }
</style>
@endpush
@section('content')
<div class="contact-page-container">
    <div class="contact-content">
        <div class="form-section">
            <h1>Gửi phản hồi</h1>
            <form id="contactForm">
                <div class="form-group">
                    <label for="name">Họ, tên</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Thông tin góp ý</label>
                    <textarea id="message" name="message" required></textarea>
                </div>

                <button type="submit" class="submit-btn">Gửi</button>
            </form>
        </div>

        <div class="contact-info-section">
            <h2>Liên hệ</h2>

            <div class="contact-page-item">
                <div class="contact-page-item-icon">
                    <img src="assets/icon-contact.png" alt="Email">
                </div>
                <div class="contact-page-item-value">support@btaskee.com</div>
            </div>

            <div class="contact-page-item">
                <div class="contact-page-item-icon">
                    <img src="assets/icon-call.png" alt="Phone">
                </div>
                <div class="contact-page-item-value">1900.636.736</div>
            </div>

            <div class="illustration-image">
                <img src="assets/hinhNguoi.png" alt="Illustration">
            </div>
        </div>
    </div>
</div>

<!-- Office Location Section - New Design -->
<div class="office-section">
    <div class="office-header-section">
        <h2>Địa chỉ văn phòng</h2>
        <p class="subtitle">Các văn phòng bTaskee tại Thành phố Hồ Chí Minh</p>
    </div>

    <div style="text-align: center;">
        <div class="city-title">Hồ Chí Minh</div>
    </div>

    <div class="office-info-bar">
        <div class="office-info-item">
            <span>⏰</span>
            <span>Thứ 2 – Thứ 7 / 08:30 ~ 18:00</span>
        </div>
        <div class="office-info-item">
            <span>📞</span>
            <span>1900.636.736</span>
        </div>
    </div>

    <div class="addresses-grid">
        <div class="address-card">
            <div class="address-number">1</div>
            <p>284/25/20 Lý Thường Kiệt, Phường Điện Hồng, TP. Hồ Chí Minh.</p>

        </div>

        <div class="address-card">
            <div class="address-number">2</div>
            <p>Tầng 4, tòa nhà HQ Tower – Số 201 Trần Não, Phường An Khánh, Tp. Hồ Chí Minh.</p>

        </div>

        <div class="address-card">
            <div class="address-number">3</div>
            <p>Tầng 4 – Số 304 Nguyễn Văn Lượng, Phường 16, Quận Gò Vấp.</p>

        </div>

        <div class="address-card">
            <div class="address-number">4</div>
            <p>Số 59 Đường 51, Phường Tân Quy, Quận 7.</p>

        </div>

        <div class="address-card">
            <div class="address-number">5</div>
            <p>Số 85 – 87 Đường số 6, Khu dân cư Phú Hữu, Phường Phú Hữu, TP. Thủ Đức.</p>

        </div>
    </div>
</div>

<script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = {
            name: document.getElementById('name').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value,
            message: document.getElementById('message').value
        };

        console.log('Form submitted:', formData);
        alert('Cảm ơn bạn đã gửi phản hồi!');
        this.reset();
    });

    // Optional: Add map link functionality
    document.querySelectorAll('.map-icon').forEach((icon, index) => {
        icon.addEventListener('click', function() {
            console.log('Opening map for location:', index + 1);
            // You can add actual Google Maps link here
            alert('Chức năng xem bản đồ sẽ được thêm vào sau!');
        });
    });
</script>
@endsection