@extends('layouts.base')
@section('title', 'Trang chủ')
@section('content')
<!-- Header -->

    <!-- Hero Section -->
    <section class="hero">
        <h1>Đồng hành chăm sóc tổ ấm của bạn.</h1>
    </section>

    <!-- Services Grid -->
    <section class="services-grid">
        <div class="service-card">
            <div class="service-icon">
            <img src="{{ asset('assets/iconDonDepNhaBep.png') }}" alt="Dọn dẹp nhà bếp">
        </div>
            <div class="service-name">Dọn dẹp <br> nhà bếp</div>
        </div>
        <div class="service-card">
            <div class="service-icon">
            <img src="{{ asset('assets/iconDonDepPhongKhach.png') }}" alt="Dọn dẹp phòng khách">
        </div>
            <div class="service-name">Dọn dẹp <br> phòng khách</div>
        </div>
        <div class="service-card">
            <div class="service-icon">
            <img src="{{ asset('assets/iconDonDepPhongTam.png') }}" alt="Dọn dẹp nhà tắm">
        </div>
            <div class="service-name">Dọn dẹp <br> nhà tắm</div>
        </div>
        <div class="service-card">
            <div class="service-icon">
            <img src="{{ asset('assets/iconDonDepPhongNgu.png') }}" alt="Dọn dẹp  phòng ngủ">
        </div>
            <div class="service-name">Dọn dẹp <br> phòng ngủ</div>
        </div>
    </section>

    @if($showNewCustomerVoucher ?? true)
        <!-- New Customer Voucher -->
        <div class="voucher-banner">
            <div class="voucher-content">
                <div class="voucher-text">
                    <span class="voucher-title">Nhập ngay mã "KHACHHANGMOI" khi đặt đơn để nhận ưu đãi cho lần đầu đặt lịch</span>
                    <span class="voucher-desc">Giảm 20%, tối đa 100.000đ</span>
                </div>
            </div>
        </div>
    @endif

    <!-- CTA Buttons -->
        <section class="cta-section">
        <a href="{{ url('select-address') }}" class="btn-book-link">
            <button class="btn-book">Đặt dịch vụ</button>
        </a>
        <a href="{{ url('giupviectheogio') }}" class="btn-find-link">
            <button class="btn-find">Khám phá dịch vụ</button>
        </a>
    </section>

    <!-- Worker CTA -->
    <section class="worker-cta">
        Bạn muốn trở thành đối tác? <a href="{{ url('workerintroduction') }}">Đăng ký ngay</a>
    </section>

    <!-- What We Offer Section -->
    <section class="what-we-offer">
        <div class="what-we-offer-subtitle">NHỮNG GÌ CHÚNG TÔI MANG ĐẾN</div>
        <h2>Không chỉ là một nền tảng dịch vụ giúp việc.</h2>
        <p class="what-we-offer-description">
            Chỉ cần một vài thao tác, bạn sẽ kết nối được với người giúp việc đáng tin cậy — phù hợp nhu cầu của bạn, từ công việc nhỏ đến chăm sóc nhà cửa thường xuyên.
        </p>
        <div class="offers-grid">
            <div class="offer-card">
                <div class="offer-icon">
    <img src="{{ asset('assets/iconLich.png') }}" alt="Lịch">
				</div>
                <h3>Đặt dịch vụ dễ dàng</h3>
                <p>Đặt lịch linh hoạt, phù hợp với nhịp sống của bạn.</p>
            </div>
            <div class="offer-card">
<div class="offer-icon">
    <img src="{{ asset('assets/iconNhanVienCN.png') }}" alt="Nhân viên" style="width:90px; height:90px; object-fit:contain;">
</div>

                <h3>Đội ngũ chuyên nghiệp và tin cậy</h3>
                <p>Tất cả nhân viên đều được xác minh lý lịch, đào tạo nghiệp vụ và đánh giá minh bạch từ khách hàng.</p>
            </div>
            <div class="offer-card">
                <div class="offer-icon">
    <img src="{{ asset('assets/iconCSKH.png') }}" alt="CSKH" style="width:90px; height:90px; object-fit:contain;">
</div>
                <h3>Trung tâm hỗ trợ</h3>
                <p>Chúng tôi hỗ trợ bạn trong suốt quá trình sử dụng dịch vụ — từ đặt lịch, đổi nhân viên đến phản hồi chất lượng.</p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
        <div class="how-it-works-container">
            <div class="how-it-works-content">
                <div class="how-it-works-subtitle">CÁCH NÓ HOẠT ĐỘNG</div>
                <h2>Giữ cho ngôi nhà gọn gàng chưa bao giờ <br> dễ dàng đến vậy.</h2>
                <p class="how-it-works-description">
                    Mọi thao tác đặt lịch và quản lý đều có thể thực hiện trên ứng dụng hoặc website - nhanh chóng và tiện lợi. Mọi thao tác đặt lịch và quản lý đều có thể thực hiện trên ứng dụng hoặc website - nhanh chóng và tiện lợi.
                </p>
            </div>

            <div class="how-it-works-visual">
    <div class="stylized-steps">
        <div class="stylized-step">
            <img src="{{ asset('assets/iconChonDV.png') }}" alt="Chọn dịch vụ" class="step-icon">
            <div class="step-title">Chọn dịch vụ</div>
            <p class="step-description">Chúng tôi có tới 9 dịch vụ sẵn sàng hỗ trợ.</p>
            <div class="step-arrow">→</div>
        </div>

        <div class="stylized-step">
            <img src="{{ asset('assets/iconChonTG.png') }}" alt="Chọn thời gian" class="step-icon">
            <div class="step-title">Chọn thời gian</div>
            <p class="step-description">Xác định ngày, giờ chưa đầy 60 giây.</p>
            <div class="step-arrow">→</div>
        </div>

        <div class="stylized-step">
            <img src="{{ asset('assets/iconTHDV.png') }}" alt="Tiến hành công việc" class="step-icon">
            <div class="step-title">Tiến hành công việc</div>
            <p class="step-description">Chất lượng 100% được đảm bảo.</p>
            <div class="step-arrow">→</div>
        </div>

        <div class="stylized-step">
            <img src="{{ asset('assets/iconDG.png') }}" alt="Đánh giá và xếp hạng" class="step-icon">
            <div class="step-title">Đánh giá và xếp hạng</div>
            <p class="step-description">Đánh giá dịch vụ qua app bTaskee.</p>
        </div>
    </div>
</div>

        </div>
    </section>

    <!-- MyHome Hub Section - CŨ -->
    <section class="myhome-hub">
        <div class="myhome-hub-container">
            <div class="myhome-hub-content">
                <div class="myhome-hub-subtitle">An tâm với lựa chọn của bạn</div>
                <h2>Với hơn 1,000,000+ khách hàng sử dụng ứng dụng bTaskee</h2>
                <p class="myhome-hub-description">
                    Sự tin tưởng của khách hàng là thước đo lớn nhất đối với chúng tôi. Từ các gia đình bận rộn, người đi làm đến những người lớn tuổi cần hỗ trợ trong sinh hoạt, dịch vụ đã trở thành lựa chọn quen thuộc trong việc chăm sóc và sắp xếp nhà cửa mỗi ngày.
                </p>
            </div>

            <div class="showcase-image">
    <iframe 
        src="https://www.youtube.com/embed/3uc1USyvrJQ  " 
        title="YouTube video player" 
        frameborder="0" 
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
        referrerpolicy="strict-origin-when-cross-origin" 
        allowfullscreen>
    </iframe>
</div>


            <div class="features-grid">
    <div class="feature-item">
        <div class="feature-icon">
            <img src="{{ asset('assets/iconHuman.png') }}" alt="Khách hàng hài lòng">
        </div>
        <div class="feature-title">97%</div>
        <p class="feature-description">Khách hàng hài lòng</p>
    </div>
    <div class="feature-item">
        <div class="feature-icon">
            <img src="{{ asset('assets/iconCV.png') }}" alt="Công việc đã hoàn thành">
        </div>
        <div class="feature-title">5,000,000+</div>
        <p class="feature-description">Công việc đã hoàn thành</p>
    </div>
    <div class="feature-item">
        <div class="feature-icon">
            <img src="{{ asset('assets/iconGLV.png') }}" alt="Giờ làm việc">
        </div>
        <div class="feature-title">15,000,000+</div>
        <p class="feature-description">Giờ làm việc</p>
    </div>
</div>
        </div>
    </section>

    <!-- MyHome Hub Section - MỚI (Layout ngang, background trắng) -->
    <section class="myhome-hub-new">
        <div class="myhome-hub-new-container">
            <div class="myhome-hub-new-left">
                <div class="myhome-hub-new-image">
                    <img src="{{ asset('assets/hinhGTAPP.png') }}" alt="Sweepsouth App MyHome Hub">
                </div>
            </div>
            <div class="myhome-hub-new-right">
                <div class="myhome-hub-new-subtitle">MỌI THỨ TRONG MỘT ỨNG DỤNG</div>
                <h2>Dễ dàng đặt dịch vụ và theo dõi công việc trên một nền tảng duy nhất.</h2>
                <p class="myhome-hub-new-description">
                    Bạn chỉ cần chọn loại dịch vụ và thời gian mong muốn, hệ thống sẽ tự động đề xuất người giúp việc phù hợp.
Lịch làm việc, trạng thái đơn và thông tin nhân viên đều được hiển thị rõ ràng trong ứng dụng, giúp bạn theo dõi và quản lý dễ dàng.
Mọi thắc mắc hoặc vấn đề phát sinh đều được đội ngũ hỗ trợ xử lý nhanh chóng.
                </p>
                <a href="{{ url('/appintroduction') }}"> <button class="myhome-hub-new-button">Khám phá ngay</button> </a>
            </div>
        </div>
    </section>

    <!-- Meet SweepStars Section -->
    <section class="sweepstars">
        <div class="sweepstars-container">
            <div class="sweepstars-header">
                <h2>Làm quen với những người sẽ đồng hành cùng bạn.</h2>
                <p class="sweepstars-description">
                    Dọn dẹp phòng bếp, phòng khách, phòng ngủ hay nhà tắm – bạn chỉ cần chọn, chúng tôi lo phần còn lại.
Tất cả nhân viên đều có kinh nghiệm và được kiểm duyệt rõ ràng, sẵn sàng hỗ trợ bất cứ lúc nào.
                </p>
            </div>

            <div class="sweepstars-grid">
                <div class="sweepstar-card">
                    <div class="sweepstar-image">
                        <img src="{{ asset('assets/hinhNV1.png') }}" alt="Nhân viên 1: Nguyễn Thị Thùy Linh">
                    </div>
                    <div class="sweepstar-name">Nguyễn Thị Thùy Linh</div>
                    <p class="sweepstar-quote">""Tôi rất thích thái độ chuyên nghiệp, dễ chịu và cách làm sạch rất kỹ lưỡng của cô ấy.""</p>
                    <div class="sweepstar-rating">⭐⭐⭐⭐⭐</div>
                </div>

                <div class="sweepstar-card">
                    <div class="sweepstar-image">
                        <img src="{{ asset('assets/hinhNV2.png') }}" alt="Nhân viên 2: Trần Như Ngọc">
                    </div>
                    <div class="sweepstar-name">Trần Như Ngọc</div>
                    <p class="sweepstar-quote">"Chị ấy làm việc rất chuyên nghiệp và cẩn thận, thái độ cực kỳ dễ thương."</p>
                    <div class="sweepstar-rating">⭐⭐⭐⭐⭐</div>
                </div>

                <div class="sweepstar-card">
                    <div class="sweepstar-image">
                        <img src="{{ asset('assets/hinhNV3.png') }}" alt="Nhân viên 3: Lê Thị Yến Nghi">
                    </div>
                    <div class="sweepstar-name">Lê Thị Yến Nghi</div>
                    <p class="sweepstar-quote">"Cô ấy làm cực kỳ có tâm, thậm chí còn lau cả gương. Tủ lạnh nhà tôi lại sạch bong như mới!"</p>
                    <div class="sweepstar-rating">⭐⭐⭐⭐⭐</div>
                </div>
            </div>

            <!-- Safety and Security Section -->
            <div class="safety-section">
                <h3>An toàn và bảo mật là ưu tiên hàng đầu của chúng tôi.</h3>
                <p class="safety-description">
                    Chúng tôi kết nối bạn với những cá nhân chăm chỉ, đáng tin cậy, đã được kiểm duyệt, đánh giá và có kinh nghiệm.
                </p>
                <div class="safety-grid">
                    <div class="safety-item">
                        <div class="safety-icon">
   							 <img src="{{ asset('assets/ratings.svg') }}" alt="Ratings">
						</div>
                        <div class="safety-title">Đánh giá và nhận xét <br> từ người dùng khác.</div>
                    </div>
                    <div class="safety-item">
                        <div class="safety-icon">
   							 <img src="{{ asset('assets/reference-checks-1.svg') }}" alt="Profile">
						</div>
                        <div class="safety-title">Kiểm tra hồ sơ <br> và tham khảo đầy đủ.</div>
                    </div>
                    <div class="safety-item">
                        <div class="safety-icon">
   							 <img src="{{ asset('assets/work-experience.svg') }}" alt="Experience">
						</div>
                        <div class="safety-title">Yêu cầu tối thiểu 2 năm <br> kinh nghiệm trước khi tham gia.</div>
                    </div>
                    <div class="safety-item">
                        <div class="safety-icon">
   							 <img src="{{ asset('assets/insurance-1.svg') }}" alt="Insurance">
						</div>
                        <div class="safety-title">Chính sách bảo hiểm đảm bảo sự an tâm cho khách hàng.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Want to Work Section -->
    <section class="want-to-work">
        <div class="want-to-work-container">
            <div class="want-to-work-left">
                <h2>Muốn làm việc cùng bTaskee? </h2>
                <p>Trở thành “ông chủ” của chính mình, tự chọn lịch làm việc và làm việc ở những khu vực bạn yêu thích.</p>
                <a href="{{ url('/workerintroduction') }}"> <button class="want-to-work-button">Ứng tuyển ngay</button> </a>
            </div>
            <div class="want-to-work-right">
                <div class="want-to-work-image">
                    <img src="{{ asset('assets/hinhGV.png') }}" alt="Workers">
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <section class="blog-section">
        <div class="blog-container">
            <div class="blog-header">
                <div class="blog-title-group">
                    <h2>Cập nhật tin tức về chúng tôi!</h2>
                    <p class="blog-subtitle">Thông cáo báo chí</p>
                </div>
            </div>

            <div class="blog-grid">
    <!-- Card 1 -->
    <a href="{{ url('/post-detail-1') }}" style="text-decoration: none; color: inherit;">
        <div class="blog-card">
            <div class="blog-image">
                <img src="{{ asset('assets/baiBao1.jpg') }}" alt="Bài báo 1">
            </div>
            <div class="blog-content">
                <div class="blog-meta">
                    <span class="blog-meta-author">bTaskee Cleaning House</span>
                    <span class="blog-meta-date">Tháng Sáu 25, 2025</span>
                </div>
                <h3>bTaskee ra mắt bBeauty, đánh dấu bước tiến mới trong hệ sinh thái dịch vụ gia đình</h3>
                <p>Ngày 25 tháng 6 năm 2025, bTaskee chính thức ra mắt bBeauty – dịch vụ làm đẹp tại nhà tích hợp trên ứng dụng...</p>
                <span class="blog-read-more">Xem thêm</span>
            </div>
        </div>
    </a>

    <!-- Card 2 -->
    <a href="{{ url('/post-detail-2') }}" style="text-decoration: none; color: inherit;">
        <div class="blog-card">
            <div class="blog-image">
                <img src="{{ asset('assets/baiBao2.jpg') }}" alt="Bài báo 2">
            </div>
            <div class="blog-content">
                <div class="blog-meta">
                    <span class="blog-meta-author">bTaskee Cleaning House</span>
                    <span class="blog-meta-date">Tháng Một 13, 2025</span>
                </div>
                <h3>bTaskee Chính Thức Ra Mắt Dịch Vụ Vệ Sinh Công Nghiệp</h3>
                <p>bTaskee đã chính thức ra mắt dịch vụ vệ sinh công nghiệp, mở rộng hệ sinh thái dịch vụ gia đình để đáp ứng nhu cầu ngày càng cao của khách hàng.</p>
                <span class="blog-read-more">Xem thêm</span>
            </div>
        </div>
    </a>

    <!-- Card 3 -->
    <a href="{{ url('/post-detail-3') }}" style="text-decoration: none; color: inherit;">
        <div class="blog-card">
            <div class="blog-image">
                <img src="{{ asset('assets/baiBao3.jpg') }}" alt="Bài báo 3">
            </div>
            <div class="blog-content">
                <div class="blog-meta">
                    <span class="blog-meta-author">bTaskee Cleaning House</span>
                    <span class="blog-meta-date">Tháng Sáu 14, 2025</span>
                </div>
                <h3>bTaskee ký kết hợp tác đào tạo và tuyển dụng với Trường Cao đẳng Văn Lang Sài Gòn</h3>
                <p>Sáng ngày 14/6/2025, trong khuôn khổ chuỗi sự kiện VLSC Career 2025 và hội thảo khoa học...</p>
                <span class="blog-read-more">Xem thêm</span>
            </div>
        </div>
    </a>

            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="faq-container">
            <div class="faq-header">
                <h2>Câu hỏi thường gặp</h2>
            </div>

            <div class="faq-accordion">
                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-question-text">1. Ứng dụng bTaskee đang được triển khai ở đâu?</div>
                        <div class="faq-toggle">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Hiện tại, ứng dụng bTaskee đã được triển khai tại hơn 20 tỉnh thành trên toàn quốc. Trong thời gian tới, bTaskee sẽ tiếp tục mở rộng hoạt động đến nhiều thành phố khác trong thời gian sớm nhất.</p>
                        <p>Bên cạnh thị trường Việt Nam, bTaskee cũng đã có mặt tại 3 quốc gia Đông Nam Á gồm: Thái Lan, Indonesia và Malaysia, và đang không ngừng mở rộng ra thị trường quốc tế.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-question-text">2. Mình muốn sử dụng thử dịch vụ của bTaskee nhưng phần vấn về chất lượng dịch vụ có tốt hay không?</div>
                        <div class="faq-toggle">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Chất lượng dịch vụ của chúng tôi được đảm bảo 100%. Tất cả các nhân viên đều được đào tạo kỹ lưỡng và trải qua quy trình kiểm duyệt nghiêm ngặt. Ngoài ra, bạn có thể xem đánh giá từ các khách hàng khác để biết thêm thông tin.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-question-text">3. Khi đăng công việc lên ứng dụng thì phải mất bao lâu mới có người nhận việc?</div>
                        <div class="faq-toggle">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Bình thường, bạn sẽ có người nhận việc trong vòng 60 phút kể từ khi đăng công việc. Thời gian này có thể còn nhanh hơn tùy thuộc vào vị trí và loại dịch vụ bạn yêu cầu.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-question-text">4. Làm sao nhân dạng người giúp việc?</div>
                        <div class="faq-toggle">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Tất cả các nhân viên trên nền tảng bTaskee đều được xác minh danh tính thông qua hệ thống kiểm duyệt chặt chẽ của chúng tôi. Bạn có thể xem thông tin chi tiết, ảnh đại diện và đánh giá từ khách hàng khác trước khi lựa chọn.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-question-text">5. Vào ngày Lễ, Tết, người giúp việc có đến đơn đẹp nhà không?</div>
                        <div class="faq-toggle">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>BTaskee vận hành 24/7 bao gồm cả các ngày lễ và Tết. Tuy nhiên, do nhiều nhân viên có thể muốn nghỉ ngơi vào những ngày này nên sẽ có ít người sẵn sàng làm việc. Chúng tôi khuyến khích bạn đặt lịch trước để đảm bảo có người nhận việc.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <div class="faq-question-text">6. Làm thế nào để sử dụng dịch vụ trên Ứng dụng một cách hoàn hảo?</div>
                        <div class="faq-toggle">+</div>
                    </div>
                    <div class="faq-answer">
                        <p>Để sử dụng dịch vụ trên ứng dụng hiệu quả, bạn nên:</p>
                        <p>1. Tải và cài đặt ứng dụng bTaskee<br>
                        2. Tạo tài khoản và xác minh thông tin cá nhân<br>
                        3. Chọn dịch vụ phù hợp với nhu cầu<br>
                        4. Đặt lịch và chờ người nhận việc<br>
                        5. Thanh toán và đánh giá dịch vụ sau khi hoàn thành</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-item">
                <div class="contact-icon">
    <img src="{{ asset('assets/iconCall.png') }}" alt="Phone Icon">
</div>
                <h3>Gọi chúng tôi</h3>
                <p>Hỗ trợ khách hàng 24/7</p>
                <a href="tel:1900123456">1900 636 736</a>
            </div>
            <div class="contact-item">
                <div class="contact-icon">
    <img src="{{ asset('assets/iconLetter.png') }}" alt="Letter">
</div>
                <h3>Email</h3>
                <p>Gửi email cho chúng tôi</p>
                <a href="mailto:support@sweepsouth.com">support@btaskee.com</a>
            </div>
            <div class="contact-item">
                <div class="contact-icon">
    <img src="{{ asset('assets/iconPlace.png') }}" alt="Place">
</div>
                <h3>Địa chỉ</h3>
                <p>Văn phòng chính</p>
                <a href="https://maps.app.goo.gl/nXUVHn2cR4Z6NGpo7  ">284/25/20 Lý Thường Kiệt, Phường Diên Hồng, TP. Hồ Chí Minh 72506</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
@endsection

<style>
/* Voucher Banner Styles - Icon Removed, Centered, Shorter & Bolder */
.voucher-banner {
    background: #F8F6F2;
    border: 1px solid #E8E2D5;
    border-radius: 8px;
    padding: 4px 12px;
    margin: 12px auto 8px;
    max-width: 60%; /* Shorter width */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.voucher-content {
    display: flex;
    align-items: center;
    justify-content: center; /* Center content horizontally */
}

.voucher-text {
    display: flex;
    flex-direction: column;
    align-items: center; /* Center text */
    gap: 1px;
}

.voucher-title {
    font-weight: 700; /* Extra bold for prominence */
    font-size: 15px;
    color: #3A3935;
    letter-spacing: 0.5px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    margin-bottom: 2px;
}

.voucher-desc {
    font-size: 14px;
    color: #D9534F; /* Stronger red accent for advertisement feel */
    font-weight: 700; /* Bold */
}

/* Responsive */
@media (max-width: 480px) {
    .voucher-banner {
        padding: 3px 10px;
        margin: 10px auto 6px;
        max-width: 90%;
    }
    
    .voucher-title {
        font-size: 11px;
    }
    
    .voucher-desc {
        font-size: 10px;
    }
}
</style>