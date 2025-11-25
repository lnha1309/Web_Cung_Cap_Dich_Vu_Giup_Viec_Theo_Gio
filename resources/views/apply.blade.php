<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng Ký Ứng Tuyển Nhân Viên</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      padding: 20px;
      max-width: 800px;
      margin: 0 auto;
      background: #f8f9fa;
    }

    h1 {
      color: #004d2e;
      margin-bottom: 30px;
      text-align: center;
    }
    
    h2 {
      color: #004d2e;
      margin: 25px 0 15px;
      font-size: 1.3em;
    }
    
    .step {
      display: none;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .step.active {
      display: block;
    }
    
    .guide-steps p {
      padding: 8px 0;
      font-size: 15px;
    }
    
    .notice {
      border-left: 4px solid #004d2e;
      padding: 15px 20px;
      background: #f0f8f5;
      margin: 20px 0;
      border-radius: 4px;
    }
    
    .notice ul {
      margin-left: 20px;
      margin-top: 10px;
    }
    
    .notice li {
      margin: 8px 0;
    }
    
    .form-group {
      margin: 20px 0;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
    }
    
    input[type="text"],
    input[type="email"],
    input[type="date"],
    input[type="number"],
    input[type="file"],
      input[type="password"],
    select,
    textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid #ddd;
      border-radius: 6px;
      font-size: 15px;
      transition: border-color 0.3s;
      font-family: Arial, sans-serif;
    }
    
    input[type="file"] {
      padding: 8px;
    }
    
    textarea {
      min-height: 100px;
      resize: vertical;
    }
    
    input:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: #004d2e;
    }
    
    .verification-group {
      display: flex;
      gap: 10px;
      align-items: flex-start;
    }
    
    .verification-group input {
      flex: 1;
    }
    
    .verification-group button {
      min-width: 140px;
      margin-top: 0;
    }
    
    button {
      background: #004d2e;
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      margin: 10px 5px 10px 0;
      transition: background 0.3s;
    }
    
    button:hover:not(:disabled) {
      background: #003d24;
    }
    
    button:disabled {
      background: #ccc;
      cursor: not-allowed;
      opacity: 0.6;
    }
    
    .verification-section {
      display: none;
      margin-top: 15px;
      padding: 15px;
      background: #f0f8f5;
      border-radius: 6px;
      border: 2px solid #c8e6c9;
    }
    
    .captcha-container {
      margin: 25px 0;
      padding: 20px;
      border: 2px solid #ddd;
      border-radius: 8px;
      text-align: center;
      background: #fafafa;
    }
    
    .captcha-container p {
      margin-bottom: 15px;
      font-weight: 600;
    }
    
    #captchaCanvas {
      border: 2px solid #ddd;
      border-radius: 4px;
      margin: 10px 0;
      cursor: pointer;
    }
    
    .captcha-input {
      width: 200px;
      margin: 10px 0;
      padding: 10px;
      text-align: center;
      font-size: 18px;
      letter-spacing: 3px;
    }
    
    .error {
      color: #dc3545;
      font-size: 14px;
      margin-top: 5px;
      display: none;
    }
    
    .success-text {
      color: #28a745;
      font-size: 14px;
      margin-top: 5px;
      display: none;
      font-weight: 600;
    }
    
    .info-text {
      color: #0066cc;
      font-size: 13px;
      margin-top: 5px;
      font-style: italic;
    }
    
    .warning-box {
      background: #fff3cd;
      border: 2px solid #ffc107;
      padding: 15px;
      margin: 20px 0;
      border-radius: 6px;
      font-weight: 600;
    }
    
    .success-message {
      text-align: center;
      padding: 60px 20px;
    }
    
    .success-message h2 {
      color: #28a745;
      font-size: 2em;
      margin-bottom: 20px;
    }
    
    .success-message p {
      font-size: 18px;
      margin: 20px 0;
    }

    .verification-code {
      font-family: 'Courier New', monospace;
      font-size: 16px;
      letter-spacing: 2px;
    }

    .loading {
      display: none;
      text-align: center;
      padding: 20px;
      color: #004d2e;
      font-weight: 600;
    }

    .loading.show {
      display: block;
    }

    .spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #004d2e;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 20px auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .file-info {
      margin-top: 10px;
      padding: 10px;
      background: #e8f5e9;
      border-radius: 4px;
      font-size: 14px;
    }
    function togglePassword(fieldId) {
    var input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
    
    @media (max-width: 768px) {
      body {
        padding: 10px;
      }
      
      .step {
        padding: 20px;
      }
      
      h1 {
        font-size: 24px;
      }
      
      .verification-group {
        flex-direction: column;
      }
      
      .verification-group button {
        width: 100%;
        margin-top: 10px;
      }
      
      .captcha-container {
        padding: 15px;
      }
      
      #captchaCanvas {
        max-width: 100%;
        height: auto;
      }
      
      .captcha-input {
        width: 100%;
      }
      
      button {
        width: 100%;
        margin-right: 0;
      }
    }
  </style>
</head>
<body>
  <!-- BƯỚC 1: ĐĂNG KÝ TÀI KHOẢN -->
  <div id="step1" class="step active">
    <h1>CHÀO MỪNG BẠN ĐĂNG KÝ ỨNG TUYỂN</h1>

    <h2>HƯỚNG DẪN ĐĂNG KÝ</h2>
    <div class="guide-steps">
      <p><strong>Bước 1:</strong> Tạo tài khoản nhân viên với xác thực 2 lớp (OTP + Email)</p>
      <p><strong>Bước 2:</strong> Điền đầy đủ thông tin chi tiết ứng tuyển</p>
      <p><strong>Bước 3:</strong> Hoàn tất và chờ thông báo từ HR</p>
    </div>

    <div class="notice">
      <h2>LƯU Ý QUAN TRỌNG</h2>
      <ul>
        <li>Mỗi ứng viên chỉ được đăng ký <strong>1 LẦN DUY NHẤT</strong></li>
        <li>Số điện thoại và Email <strong>KHÔNG THỂ SỬA ĐỔI</strong> sau khi đăng ký</li>
        <li>Bạn phải xác thực cả <strong>OTP (SMS)</strong> và <strong>Mã xác thực Email</strong></li>
        <li>Vui lòng kiểm tra kỹ thông tin trước khi gửi</li>
        <li>Thông báo kết quả sẽ được gửi qua điện thoại và email</li>
      </ul>
    </div>

    <form id="registerForm">
      <div class="form-group">
        <label for="username">Tên đăng nhập *</label>
        <input type="text" id="username" placeholder="Tên đăng nhập của bạn" required>
      </div>
      <div class="form-group" style="position: relative;">
    <label for="password">Mật khẩu *</label>
    <input type="password" id="password" placeholder="Nhập mật khẩu của bạn" required>
    <span class="toggle-password" onclick="togglePassword('password')" style="position: absolute; right: 10px; top: 44px; cursor: pointer;">
        <!-- SVG icon mắt -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
        </svg>
    </span>
</div>
<div class="form-group" style="position: relative;">
    <label for="confirmPassword">Xác nhận mật khẩu *</label>
    <input type="password" id="confirmPassword" placeholder="Nhập lại mật khẩu của bạn" required>
    <span class="toggle-password" onclick="togglePassword('confirmPassword')" style="position: absolute; right: 10px; top: 44px; cursor: pointer;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22">
            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
        </svg>
    </span>
</div>

      <div class="form-group">
        <label for="fullname">Họ và tên *</label>
        <input type="text" id="fullname" placeholder="Nguyễn Văn A" required>
      </div>
      <div class="form-group">
        <label for="dob">Ngày sinh *</label>
        <input type="date" id="dob" required>
        <span class="error" id="dobError">Bạn phải đủ 18 tuổi để đăng ký</span>
      </div>

      <div class="form-group">
        <label for="gender">Giới tính *</label>
        <select id="gender" required>
          <option value="">-- Chọn giới tính --</option>
          <option value="Nam">Nam</option>
          <option value="Nữ">Nữ</option>
        </select>
      </div>

      <div class="form-group">
        <label for="experience">Khu vực *</label>
        <select id="experience" required>
          <option value="">-- Chọn khu vực bạn sẽ làm việc --</option>
          <option value="Quận 1">Quận 1</option>
          <option value="Quận 2">Quận 2</option>
          <option value="Quận 3">Quận 3</option>
          <option value="Quận 4">Quận 4</option>
          <option value="Quận 5">Quận 5</option>
          <option value="Quận 6">Quận 6</option>
          <option value="Quận 7">Quận 7</option>
          <option value="Quận 8">Quận 8</option>
          <option value="Quận 10">Quận 10</option>
          <option value="Quận 11">Quận 11</option>
          <option value="Quận 12">Quận 12</option>
          <option value="TP Thủ Đức">TP Thủ Đức</option>
          <option value="Quận Bình Tân">Quận Bình Tân</option>
          <option value="Quận Tân Bình">Quận Tân Bình</option>
          <option value="Quận Tân Phú">Quận Tân Phú</option>
          <option value="Quận Gò Vấp">Quận Gò Vấp</option>
          <option value="Quận Phú Nhuận">Quận Phú Nhuận</option>
          <option value="Quận Bình Thạnh">Quận Bình Thạnh</option>
          <option value="Huyện Củ Chi">Huyện Củ Chi</option>
          <option value="Huyện Hóc Môn">Huyện Hóc Môn</option>
          <option value="Huyện Bình Chánh">Huyện Bình Chánh</option>
          <option value="Huyện Nhà Bè">Huyện Nhà Bè</option>
          <option value="Huyện Cần Giờ">Huyện Cần Giờ</option>
        </select>
      </div>

      <div class="form-group">
        <label for="email">Email *</label>
        <div class="verification-group">
          <input type="email" id="email" placeholder="example@gmail.com" required>
          <button type="button" id="sendEmailCodeBtn">Gửi Mã Email</button>
        </div>
        <span class="error" id="emailError">Email không hợp lệ</span>
        <span class="success-text" id="emailSuccess">✓ Mã xác thực đã được gửi đến email!</span>
        <p class="info-text">Mã xác thực sẽ được gửi đến email của bạn</p>
      </div>

      <div class="verification-section" id="emailCodeSection">
        <div class="form-group">
          <label for="emailCode">Nhập Mã Xác Thực Email (6 chữ số) *</label>
          <input type="text" id="emailCode" maxlength="6" placeholder="Nhập 6 chữ số từ email" class="verification-code">
          <span class="error" id="emailCodeError">Mã xác thực email không đúng</span>
          <span class="success-text" id="emailCodeSuccess">✓ Xác thực email thành công!</span>
        </div>
      </div>

      <div class="form-group">
        <label for="phone">Số điện thoại *</label>
        <div class="verification-group">
          <input type="text" id="phone" placeholder="0912345678" required>
          <button type="button" id="sendOtpBtn">Gửi OTP</button>
        </div>
        <span class="error" id="phoneError">Số điện thoại không hợp lệ (VD: 0912345678)</span>
        <span class="success-text" id="phoneSuccess">✓ Mã OTP đã được gửi!</span>
        <p class="info-text">Mã OTP sẽ được gửi qua tin nhắn SMS</p>
      </div>

      <div class="verification-section" id="otpSection">
        <div class="form-group">
          <label for="otpCode">Nhập Mã OTP (6 chữ số) *</label>
          <input type="text" id="otpCode" maxlength="6" placeholder="Nhập 6 chữ số từ SMS" class="verification-code">
          <span class="error" id="otpError">Mã OTP không đúng</span>
          <span class="success-text" id="otpSuccess">✓ Xác thực OTP thành công!</span>
        </div>
      </div>

      <div class="captcha-container">
        <p>Xác Thực CAPTCHA</p>
        <canvas id="captchaCanvas" width="260" height="80"></canvas><br>
        <input type="text" id="captchaInput" class="captcha-input" placeholder="Nhập mã bảo mật" maxlength="6">
        <br>
        <button type="button" id="refreshCaptchaBtn">Làm mới</button>
        <span class="error" id="captchaError">Mã CAPTCHA không đúng</span>
        <span class="success-text" id="captchaSuccess">✓ CAPTCHA chính xác!</span>
      </div>

      <button type="submit" id="submitBtn" disabled>Đăng ký tài khoản</button>
      <p class="info-text" style="margin-top: 10px;">* Nút đăng ký sẽ được kích hoạt khi bạn xác thực đầy đủ: Email + OTP + CAPTCHA</p>
    </form>
  </div>

  <!-- BƯỚC 2: ĐIỀN THÔNG TIN CHI TIẾT -->
  <div id="step2" class="step">
    <h1>ĐIỀN THÔNG TIN CHI TIẾT</h1>
    
    <div class="warning-box">
       <strong>BẮT BUỘC:</strong> Bạn phải hoàn thành tất cả thông tin bên dưới để hoàn tất đăng ký!
    </div>
    
    <form id="detailForm">
      <div class="form-group">
        <label for="position">Vị trí ứng tuyển *</label>
        <select id="position" required>
          <option value="">-- Chọn vị trí ứng tuyển --</option>
          <option value="Nhân viên vệ sinh">Nhân viên vệ sinh</option>
          <option value="Nhân viên nấu ăn">Nhân viên nấu ăn</option>
          <option value="Nhân viên vệ sinh máy lạnh">Nhân viên vệ sinh máy lạnh</option>
          <option value="Nhân viên giặt ủi">Nhân viên giặt ủi</option>
        </select>
      </div>

      <div class="form-group">
        <label for="address">Địa chỉ hiện tại *</label>
        <textarea id="address" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" required></textarea>
      </div>

      <div class="form-group">
        <label for="education">Trình độ học vấn *</label>
        <select id="education" required>
          <option value="">-- Chọn trình độ --</option>
          <option value="THCS">THCS</option>
          <option value="THPT">THPT</option>
          <option value="Cao đẳng">Cao đẳng</option>
          <option value="Đại học">Đại học</option>
          <option value="Khác">Khác</option>
        </select>
      </div>

      <div class="form-group">
        <label for="resumeFile">Sơ yếu lý lịch (Ảnh hoặc PDF) *</label>
        <input type="file" id="resumeFile" accept="image/*,.pdf" required>
        <p class="info-text">Chấp nhận: JPG, PNG, PDF (tối đa 5MB)</p>
        <span class="error" id="fileError">File quá lớn hoặc định dạng không hợp lệ</span>
        <div id="fileInfo" class="file-info" style="display: none;"></div>
      </div>

      <div class="form-group">
        <label for="reason">Lý do ứng tuyển *</label>
        <textarea id="reason" placeholder="Tại sao bạn muốn làm việc tại đây?" required></textarea>
      </div>

      <div class="form-group">
        <label for="currentJob">Tình trạng công việc hiện tại *</label>
        <select id="currentJob" required>
          <option value="">-- Chọn tình trạng --</option>
          <option value="Đang tìm việc">Đang tìm việc</option>
          <option value="Đang đi làm">Đang đi làm</option>
          <option value="Đang học">Đang học</option>
          <option value="Freelancer">Freelancer</option>
        </select>
      </div>

      <div class="form-group">
        <label for="workType">Loại hình công việc mong muốn *</label>
        <select id="workType" required>
          <option value="">-- Chọn loại hình --</option>
          <option value="Full-time">Full-time (Toàn thời gian)</option>
          <option value="Part-time">Part-time (Bán thời gian)</option>
          <option value="Linh hoạt">Linh hoạt</option>
        </select>
      </div>

      <div class="form-group">
        <label for="expectedSalary">Mức lương mong muốn (VNĐ/tháng) *</label>
        <input type="number" id="expectedSalary" placeholder="VD: 8000000" required min="0">
      </div>

      <div class="form-group">
        <label for="feedback">Góp ý / Câu hỏi (Nếu có)</label>
        <textarea id="feedback" placeholder="Bạn có câu hỏi hay góp ý gì không?"></textarea>
      </div>

      <div class="loading" id="loadingIndicator">
        <div class="spinner"></div>
        <p>Đang tải file và gửi thông tin...</p>
      </div>

      <button type="submit" id="submitDetailBtn">✓ Hoàn tất đăng ký</button>
    </form>
  </div>

  <!-- BƯỚC 3: HOÀN TẤT -->
  <div id="step3" class="step">
    <div class="success-message">
      <h2>ĐĂNG KÝ THÀNH CÔNG</h2>
      <p>Chúc mừng bạn đã hoàn tất đăng ký ứng tuyển</p>
      <p>Vui lòng chú ý điện thoại và email để nhận thông báo từ HR</p>
      <p>Chúng tôi sẽ liên hệ với bạn trong vòng 3-5 ngày làm việc</p>
      <button type="button" onclick="location.reload()">Về trang chủ</button>
    </div>
  </div>

  <script>
    // URL Google Apps Script Web App
    const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyZoPtQxxZGUAJGOPrtuZu19l5wH64oiKQ7QFdhRhXtsNwygQ3kjmVoz-CpcpHBEN28GA/exec";

    // Biến toàn cục
    let generatedCaptcha = "";
    let generatedOtp = "";
    let generatedEmailCode = "";
    let otpVerified = false;
    let emailCodeVerified = false;
    let captchaVerified = false;
    let emailCooldown = false;
    let phoneCooldown = false;
    let selectedFile = null;

    // Lưu thông tin bước 1
    let step1Data = {};

    // Hàm tạo ký tự ngẫu nhiên cho CAPTCHA
    function randomChar() {
      const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789";
      return chars[Math.floor(Math.random() * chars.length)];
    }

    // Hàm vẽ CAPTCHA
    function drawCaptcha() {
      const canvas = document.getElementById("captchaCanvas");
      const ctx = canvas.getContext("2d");
      const w = canvas.width;
      const h = canvas.height;
      
      ctx.clearRect(0, 0, w, h);
      
      const gradient = ctx.createLinearGradient(0, 0, w, h);
      gradient.addColorStop(0, "#e8f5e9");
      gradient.addColorStop(1, "#c8e6c9");
      ctx.fillStyle = gradient;
      ctx.fillRect(0, 0, w, h);
      
      generatedCaptcha = "";
      for (let i = 0; i < 6; i++) {
        generatedCaptcha += randomChar();
      }
      
      let x = 30;
      for (let i = 0; i < generatedCaptcha.length; i++) {
        const char = generatedCaptcha[i];
        const rotation = (Math.random() - 0.5) * 30;
        
        ctx.save();
        ctx.translate(x, 50);
        ctx.rotate(rotation * Math.PI / 180);
        ctx.font = `${30 + Math.random() * 10}px Arial Black`;
        ctx.fillStyle = `rgb(${Math.random()*80}, ${Math.random()*80}, ${Math.random()*80})`;
        ctx.fillText(char, 0, 0);
        ctx.restore();
        
        x += 35;
      }
      
      for (let i = 0; i < 150; i++) {
        ctx.fillStyle = `rgba(${Math.random()*255}, ${Math.random()*255}, ${Math.random()*255}, 0.3)`;
        ctx.fillRect(Math.random() * w, Math.random() * h, 2, 2);
      }
      
      for (let i = 0; i < 5; i++) {
        ctx.strokeStyle = `rgba(${Math.random()*150}, ${Math.random()*150}, ${Math.random()*150}, 0.5)`;
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(Math.random() * w, Math.random() * h);
        ctx.lineTo(Math.random() * w, Math.random() * h);
        ctx.stroke();
      }
      
      console.log("CAPTCHA Generated:", generatedCaptcha);
      captchaVerified = false;
      checkFormComplete();
    }

    // Xử lý file upload
    document.getElementById("resumeFile").addEventListener("change", function(e) {
      const file = e.target.files[0];
      const fileInfo = document.getElementById("fileInfo");
      const fileError = document.getElementById("fileError");
      
      if (!file) {
        selectedFile = null;
        fileInfo.style.display = "none";
        return;
      }
      
      // Kiểm tra kích thước file (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        fileError.style.display = "block";
        fileError.textContent = "⚠️ File quá lớn! Vui lòng chọn file dưới 5MB";
        this.value = "";
        selectedFile = null;
        fileInfo.style.display = "none";
        return;
      }
      
      // Kiểm tra định dạng file
      const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
      if (!validTypes.includes(file.type)) {
        fileError.style.display = "block";
        fileError.textContent = "⚠️ Chỉ chấp nhận file JPG, PNG hoặc PDF";
        this.value = "";
        selectedFile = null;
        fileInfo.style.display = "none";
        return;
      }
      
      fileError.style.display = "none";
      selectedFile = file;
      
      // Hiển thị thông tin file
      const fileSize = (file.size / 1024).toFixed(2);
      fileInfo.style.display = "block";
      fileInfo.innerHTML = `✓ Đã chọn: <strong>${file.name}</strong> (${fileSize} KB)`;
    });

    // Xử lý nút Làm mới CAPTCHA
    document.getElementById("refreshCaptchaBtn").addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      drawCaptcha();
      document.getElementById("captchaInput").value = "";
      document.getElementById("captchaError").style.display = "none";
      document.getElementById("captchaSuccess").style.display = "none";
    });

    document.getElementById("captchaCanvas").addEventListener("click", function() {
      drawCaptcha();
      document.getElementById("captchaInput").value = "";
      document.getElementById("captchaError").style.display = "none";
      document.getElementById("captchaSuccess").style.display = "none";
    });

    // Gửi mã xác thực Email
    document.getElementById("sendEmailCodeBtn").addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const email = document.getElementById("email").value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      
      if (!emailRegex.test(email)) {
        document.getElementById("emailError").style.display = "block";
        document.getElementById("emailSuccess").style.display = "none";
        return;
      }
      
      document.getElementById("emailError").style.display = "none";
      
      if (emailCooldown) {
        alert("⏰ Vui lòng đợi 60 giây trước khi gửi lại mã email!");
        return;
      }
      
      generatedEmailCode = Math.floor(100000 + Math.random() * 900000).toString();
      console.log("📧 Email Code Generated:", generatedEmailCode);
      
      alert("📧 Mã xác thực email của bạn là: " + generatedEmailCode + "\n\n(Trong môi trường thực tế, mã này sẽ được gửi đến email: " + email + ")");
      
      document.getElementById("emailCodeSection").style.display = "block";
      document.getElementById("emailSuccess").style.display = "block";
      
      emailCooldown = true;
      this.disabled = true;
      let timeLeft = 60;
      this.textContent = `Đã gửi (${timeLeft}s)`;
      
      const countdown = setInterval(() => {
        timeLeft--;
        this.textContent = `Đã gửi (${timeLeft}s)`;
        
        if (timeLeft <= 0) {
          clearInterval(countdown);
          this.textContent = "Gửi Lại Mã";
          this.disabled = false;
          emailCooldown = false;
        }
      }, 1000);
      
      checkFormComplete();
    });

    document.getElementById("emailCode").addEventListener("input", function() {
      const emailCodeInput = this.value.trim();
      
      if (emailCodeInput.length === 6) {
        if (emailCodeInput === generatedEmailCode) {
          emailCodeVerified = true;
          document.getElementById("emailCodeError").style.display = "none";
          document.getElementById("emailCodeSuccess").style.display = "block";
          checkFormComplete();
        } else {
          emailCodeVerified = false;
          document.getElementById("emailCodeError").style.display = "block";
          document.getElementById("emailCodeSuccess").style.display = "none";
          checkFormComplete();
        }
      } else {
        emailCodeVerified = false;
        document.getElementById("emailCodeError").style.display = "none";
        document.getElementById("emailCodeSuccess").style.display = "none";
        checkFormComplete();
      }
    });

    // Gửi OTP
    document.getElementById("sendOtpBtn").addEventListener("click", function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      const phone = document.getElementById("phone").value.trim();
      const phoneRegex = /^(0|\+84)[3|5|7|8|9][0-9]{8}$/;
      
      if (!phoneRegex.test(phone)) {
        document.getElementById("phoneError").style.display = "block";
        document.getElementById("phoneSuccess").style.display = "none";
        return;
      }
      
      document.getElementById("phoneError").style.display = "none";
      
      if (phoneCooldown) {
        alert("⏰ Vui lòng đợi 60 giây trước khi gửi lại OTP!");
        return;
      }
      
      generatedOtp = Math.floor(100000 + Math.random() * 900000).toString();
      console.log("📱 OTP Generated:", generatedOtp);
      
      alert("📱 Mã OTP của bạn là: " + generatedOtp + "\n\n(Trong môi trường thực tế, mã này sẽ được gửi qua SMS đến số: " + phone + ")");
      
      document.getElementById("otpSection").style.display = "block";
      document.getElementById("phoneSuccess").style.display = "block";
      
      phoneCooldown = true;
      this.disabled = true;
      let timeLeft = 60;
      this.textContent = `Đã gửi (${timeLeft}s)`;
      
      const countdown = setInterval(() => {
        timeLeft--;
        this.textContent = `Đã gửi (${timeLeft}s)`;
        
        if (timeLeft <= 0) {
          clearInterval(countdown);
          this.textContent = "Gửi Lại OTP";
          this.disabled = false;
          phoneCooldown = false;
        }
      }, 1000);
      
      checkFormComplete();
    });

    document.getElementById("otpCode").addEventListener("input", function() {
      const otpInput = this.value.trim();
      
      if (otpInput.length === 6) {
        if (otpInput === generatedOtp) {
          otpVerified = true;
          document.getElementById("otpError").style.display = "none";
          document.getElementById("otpSuccess").style.display = "block";
          checkFormComplete();
        } else {
          otpVerified = false;
          document.getElementById("otpError").style.display = "block";
          document.getElementById("otpSuccess").style.display = "none";
          checkFormComplete();
        }
      } else {
        otpVerified = false;
        document.getElementById("otpError").style.display = "none";
        document.getElementById("otpSuccess").style.display = "none";
        checkFormComplete();
      }
    });

    document.getElementById("captchaInput").addEventListener("input", function() {
      const captchaInput = this.value.trim();
      
      if (captchaInput.length > 0) {
        if (captchaInput === generatedCaptcha) {
          captchaVerified = true;
          document.getElementById("captchaError").style.display = "none";
          document.getElementById("captchaSuccess").style.display = "block";
        } else {
          captchaVerified = false;
          document.getElementById("captchaError").style.display = "block";
          document.getElementById("captchaSuccess").style.display = "none";
        }
      } else {
        captchaVerified = false;
        document.getElementById("captchaError").style.display = "none";
        document.getElementById("captchaSuccess").style.display = "none";
      }
      
      checkFormComplete();
    });

    document.getElementById("dob").addEventListener("change", function() {
      const dob = new Date(this.value);
      const today = new Date();
      let age = today.getFullYear() - dob.getFullYear();
      const monthDiff = today.getMonth() - dob.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
      }
      
      if (age < 18) {
        document.getElementById("dobError").style.display = "block";
      } else {
        document.getElementById("dobError").style.display = "none";
      }
      
      checkFormComplete();
    });

    document.getElementById("email").addEventListener("input", function() {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (this.value && !emailRegex.test(this.value)) {
        document.getElementById("emailError").style.display = "block";
      } else {
        document.getElementById("emailError").style.display = "none";
      }
      checkFormComplete();
    });

    function checkFormComplete() {
      const fullname = document.getElementById("fullname").value.trim();
      const dob = document.getElementById("dob").value;
      const gender = document.getElementById("gender").value;
      const experience = document.getElementById("experience").value;
      const phone = document.getElementById("phone").value.trim();
      const email = document.getElementById("email").value.trim();
      
      let isAdult = false;
      if (dob) {
        const dobDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - dobDate.getFullYear();
        const monthDiff = today.getMonth() - dobDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dobDate.getDate())) {
          age--;
        }
        
        isAdult = age >= 18;
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const isEmailValid = emailRegex.test(email);
      
      const phoneRegex = /^(0|\+84)[3|5|7|8|9][0-9]{8}$/;
      const isPhoneValid = phoneRegex.test(phone);
      
      const isFormValid = fullname && dob && gender && experience && phone && email && 
                          isAdult && isPhoneValid && isEmailValid &&
                          emailCodeVerified && otpVerified && captchaVerified;
      
      document.getElementById("submitBtn").disabled = !isFormValid;
    }

    ["fullname", "gender", "experience", "phone"].forEach(id => {
      document.getElementById(id).addEventListener("input", checkFormComplete);
      document.getElementById(id).addEventListener("change", checkFormComplete);
    });

    document.getElementById("registerForm").addEventListener("submit", function(e) {
      e.preventDefault();
      
      if (!emailCodeVerified) {
        alert("⚠️ Vui lòng xác thực mã email trước!");
        return;
      }
      
      if (!otpVerified) {
        alert("⚠️ Vui lòng xác thực OTP trước!");
        return;
      }
      
      if (!captchaVerified) {
        alert("⚠️ Vui lòng nhập đúng mã CAPTCHA!");
        return;
      }
      
      // Lưu dữ liệu bước 1
      step1Data = {
        fullname: document.getElementById("fullname").value.trim(),
        phone: document.getElementById("phone").value.trim(),
        email: document.getElementById("email").value.trim(),
        gender: document.getElementById("gender").value,
        dob: document.getElementById("dob").value,
        experience: document.getElementById("experience").value
      };
      
      alert("✅ Đăng ký tài khoản thành công!\n\n🔐 Bạn đã hoàn thành xác thực 2 lớp:\n✓ Email Code\n✓ SMS OTP\n✓ CAPTCHA\n\n📋 Tiếp theo, vui lòng điền đầy đủ thông tin chi tiết.");
      
      document.getElementById("step1").classList.remove("active");
      document.getElementById("step2").classList.add("active");
      window.scrollTo(0, 0);
    });

    // Hàm chuyển file sang base64
    function fileToBase64(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result.split(',')[1]);
        reader.onerror = error => reject(error);
        reader.readAsDataURL(file);
      });
    }

    // Xử lý form bước 2
document.getElementById("detailForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  
  const position = document.getElementById("position").value;
  const address = document.getElementById("address").value.trim();
  const education = document.getElementById("education").value;
  const reason = document.getElementById("reason").value.trim();
  const currentJob = document.getElementById("currentJob").value;
  const workType = document.getElementById("workType").value;
  const expectedSalary = document.getElementById("expectedSalary").value;
  const feedback = document.getElementById("feedback").value.trim();
  
  // Kiểm tra các trường bắt buộc
  if (!position || !address || !education || !reason || !currentJob || !workType || !expectedSalary) {
    alert("⚠️ Vui lòng điền đầy đủ tất cả các trường bắt buộc (*)");
    return;
  }
  
  if (!selectedFile) {
    alert("⚠️ Vui lòng tải lên sơ yếu lý lịch!");
    return;
  }
  
  // Hiển thị loading
  document.getElementById("loadingIndicator").classList.add("show");
  document.getElementById("submitDetailBtn").disabled = true;
  
  try {
    console.log("🔄 Bắt đầu chuyển đổi file...");
    
    // Chuyển file sang base64
    const fileBase64 = await fileToBase64(selectedFile);
    
    console.log("✅ File đã chuyển thành base64");
    console.log("📄 File name:", selectedFile.name);
    console.log("📄 File type:", selectedFile.type);
    console.log("📄 File size:", selectedFile.size);
    console.log("📄 Base64 length:", fileBase64.length);
    
    // Kết hợp dữ liệu từ cả 2 bước
    const finalData = {
      // Bước 1
      fullname: step1Data.fullname,
      phone: step1Data.phone,
      email: step1Data.email,
      gender: step1Data.gender,
      dob: step1Data.dob,
      experience: step1Data.experience,
      
      // Bước 2
      position: position,
      address: address,
      education: education,
      resumeFile: {
        name: selectedFile.name,
        type: selectedFile.type,
        data: fileBase64
      },
      reason: reason,
      currentJob: currentJob,
      workType: workType,
      expectedSalary: expectedSalary,
      feedback: feedback
    };
    
    // Debug: Kiểm tra dữ liệu trước khi gửi
    console.log("=== DỮ LIỆU GỬI LÊN SERVER ===");
    console.log("Họ tên:", finalData.fullname);
    console.log("SĐT:", finalData.phone);
    console.log("Email:", finalData.email);
    console.log("Giới tính:", finalData.gender);
    console.log("Ngày sinh:", finalData.dob);
    console.log("Kinh nghiệm:", finalData.experience);
    console.log("Vị trí:", finalData.position);
    console.log("Địa chỉ:", finalData.address);
    console.log("Học vấn:", finalData.education);
    console.log("File:", finalData.resumeFile.name);
    console.log("File có data?:", !!finalData.resumeFile.data);
    console.log("Lý do:", finalData.reason);
    console.log("Công việc hiện tại:", finalData.currentJob);
    console.log("Loại hình:", finalData.workType);
    console.log("Lương mong muốn:", finalData.expectedSalary);
    console.log("Góp ý:", finalData.feedback);
    
    console.log("🚀 Đang gửi dữ liệu...");
    
    // Gửi dữ liệu đến Google Apps Script
    const response = await fetch(SCRIPT_URL, {
      method: 'POST',
      body: JSON.stringify(finalData)
    });
    
    console.log("📥 Đã nhận response");
    
    const result = await response.json();
    
    console.log("📊 Result:", result);
    
    document.getElementById("loadingIndicator").classList.remove("show");
    
    if (result.result === "success") {
      console.log("✅ Thành công!");
      if (result.resumeLink) {
        console.log("🔗 Link file:", result.resumeLink);
      }
      // Chuyển sang bước 3
      document.getElementById("step2").classList.remove("active");
      document.getElementById("step3").classList.add("active");
      window.scrollTo(0, 0);
    } else {
      throw new Error(result.message || "Không thể gửi dữ liệu");
    }
    
  } catch (error) {
    console.error("Error:", error);
    document.getElementById("loadingIndicator").classList.remove("show");
    document.getElementById("submitDetailBtn").disabled = false;
    alert("Có lỗi xảy ra khi gửi dữ liệu. Vui lòng thử lại!\n\nLỗi: " + error.message);
  }
});

    window.onload = function() {
      drawCaptcha();
    };
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const pos = document.getElementById('position');
      if (pos) {
        while (pos.firstChild) pos.removeChild(pos.firstChild);
        const opt = document.createElement('option');
        opt.value = 'Nhân viên dọn dẹp';
        opt.textContent = 'Nhân viên dọn dẹp';
        opt.selected = true;
        pos.appendChild(opt);
      }

      const reasonTextarea = document.getElementById('reason');
      if (reasonTextarea) {
        reasonTextarea.style.display = 'none';
        const reasonSelect = document.createElement('select');
        reasonSelect.id = 'reasonSelect';
        reasonSelect.required = true;
        reasonSelect.innerHTML = '<option value="">-- Chọn lý do phổ biến --</option>'
          + '<option value="Tăng thu nhập">Tăng thu nhập</option>'
          + '<option value="Lịch làm linh hoạt">Lịch làm linh hoạt</option>'
          + '<option value="Gần nhà">Gần nhà</option>'
          + '<option value="Học hỏi kinh nghiệm">Học hỏi kinh nghiệm</option>'
          + '<option value="Môi trường chuyên nghiệp">Môi trường chuyên nghiệp</option>'
          + '<option value="Khác">Khác</option>';
        reasonTextarea.parentNode.insertBefore(reasonSelect, reasonTextarea);
        const otherWrap = document.createElement('div');
        otherWrap.id = 'reasonOtherWrapper';
        otherWrap.style.display = 'none';
        otherWrap.style.marginTop = '10px';
        const otherLabel = document.createElement('label');
        otherLabel.htmlFor = 'reasonOther';
        otherLabel.textContent = 'Lý do khác';
        const otherInput = document.createElement('input');
        otherInput.type = 'text';
        otherInput.id = 'reasonOther';
        otherInput.placeholder = 'Nhập lý do khác';
        otherWrap.appendChild(otherLabel);
        otherWrap.appendChild(otherInput);
        reasonTextarea.parentNode.insertBefore(otherWrap, reasonTextarea.nextSibling);
        const syncReason = () => {
          const sel = reasonSelect.value;
          reasonTextarea.value = (sel === 'Khác') ? otherInput.value.trim() : sel;
        };
        reasonSelect.addEventListener('change', () => {
          if (reasonSelect.value === 'Khác') {
            otherWrap.style.display = 'block';
            otherInput.required = true;
          } else {
            otherWrap.style.display = 'none';
            otherInput.required = false;
            otherInput.value = '';
          }
          syncReason();
        });
        otherInput.addEventListener('input', syncReason);
      }

      // Address: restrict to combobox (TPHCM or Khác)
      const addressTextarea = document.getElementById('address');
      if (addressTextarea) {
        addressTextarea.style.display = 'none';
        addressTextarea.readOnly = true;
        const addressSelect = document.createElement('select');
        addressSelect.id = 'addressSelect';
        addressSelect.required = true;
        addressSelect.innerHTML = '<option value="">-- Chọn địa chỉ --</option>'
          + '<option value="TPHCM">TPHCM</option>'
          + '<option value="Khác">Khác</option>';
        addressTextarea.parentNode.insertBefore(addressSelect, addressTextarea);
        const addressLabel = document.querySelector('label[for="address"]');
        if (addressLabel) addressLabel.htmlFor = 'addressSelect';
        const syncAddress = () => { addressTextarea.value = addressSelect.value; };
        addressSelect.addEventListener('change', syncAddress);
      }

      const expectedSalary = document.getElementById('expectedSalary');
      if (expectedSalary) {
        const preset = document.createElement('select');
        preset.id = 'salaryPreset';
        preset.required = true;
        preset.style.marginBottom = '10px';
        preset.innerHTML = '<option value="5000000" selected>&gt; 5.000.000</option>'
          + '<option value="10000000">Trên 10.000.000</option>';
        expectedSalary.parentNode.insertBefore(preset, expectedSalary);
        const setSalary = () => { expectedSalary.value = preset.value; };
        preset.addEventListener('change', setSalary);
        // Lock and hide the numeric input to prevent custom values
        expectedSalary.readOnly = true;
        expectedSalary.style.display = 'none';
        setSalary();
      }
    });
    function togglePassword(fieldId) {
    var input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}

  </script>
</body>
</html>
