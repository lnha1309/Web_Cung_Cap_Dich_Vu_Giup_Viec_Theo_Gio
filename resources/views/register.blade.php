<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
        }

        .logo-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }

        .logo-wrapper img {
            max-width: 140px;
            height: auto;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-size: 32px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #004d2e;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            padding-right: 50px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }

        .toggle-password:hover {
            opacity: 0.8;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            fill: #666;
        }

        .phone-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .phone-input {
            flex: 1;
        }

        .otp-button {
            padding: 15px 25px;
            background: #004d2e;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .otp-button:hover:not(:disabled) {
            background: #003d23;
        }

        .otp-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .otp-verification {
            display: none;
            margin-top: 15px;
            padding: 20px;
            background: #f0f8f5;
            border: 2px solid #004d2e;
            border-radius: 12px;
        }

        .otp-verification.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .otp-info {
            font-size: 14px;
            color: #004d2e;
            margin-bottom: 12px;
            font-weight: 500;
        }

        .otp-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .otp-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #004d2e;
            border-radius: 8px;
            font-size: 16px;
        }

        .verify-button {
            padding: 12px 20px;
            background: #004d2e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .verify-button:hover:not(:disabled) {
            background: #003d23;
        }

        .verify-button:disabled {
            background: #ccc;
        }

        .success-message {
            color: #4caf50;
            font-size: 14px;
            margin-top: 8px;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .captcha-container {
            margin: 25px 0;
            padding: 20px;
            background: #f5f5f5;
            border: 2px solid #ddd;
            border-radius: 12px;
        }

        .captcha-box {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        #captchaCanvas {
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
        }

        .refresh-captcha {
            padding: 10px 15px;
            background: #004d2e;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .refresh-captcha:hover {
            background: #003d23;
        }

        .captcha-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
            letter-spacing: 5px;
        }

        .captcha-input:focus {
            outline: none;
            border-color: #004d2e;
        }

        .checkbox-group {
            margin: 30px 0;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #004d2e;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .checkbox-label {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
            cursor: pointer;
        }

        .checkbox-label a {
            color: #004d2e;
            text-decoration: none;
            font-weight: 600;
        }

        .checkbox-label a:hover {
            text-decoration: underline;
        }

        .register-button {
            width: 100%;
            padding: 15px;
            background: #004d2e;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .register-button:hover:not(:disabled) {
            background: #003d23;
            transform: translateY(-2px);
        }

        .register-button:active:not(:disabled) {
            transform: translateY(0);
        }

        .register-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 15px;
        }

        .login-link a {
            color: #004d2e;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #003d23;
            text-decoration: underline;
        }

        .error-message {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 8px;
            display: none;
        }

        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .password-strength.weak {
            color: #d32f2f;
            display: block;
        }

        .password-strength.medium {
            color: #ff9800;
            display: block;
        }

        .password-strength.strong {
            color: #4caf50;
            display: block;
        }

        .timer {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo-wrapper">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo">
            </a>
        </div>
        <form id="registerForm">
            <h1>Đăng ký</h1>
            
            <div class="form-group">
                <label for="fullname">Họ và tên</label>
                <input type="text" id="fullname" name="fullname" placeholder="Nhập họ và tên của bạn" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <div class="phone-group">
                    <input type="tel" id="phone" name="phone" class="phone-input" placeholder="Nhập số điện thoại của bạn" required>
                    <button type="button" class="otp-button" id="sendOtpBtn" onclick="sendOTP()">Gửi OTP</button>
                </div>
                <div class="timer" id="timer"></div>
                
                <div class="otp-verification" id="otpVerification">
                    <div class="otp-info">
                        Mã OTP đã được gửi đến số điện thoại của bạn
                    </div>
                    <div class="otp-input-group">
                        <input type="text" id="otpCode" class="otp-input" placeholder="Nhập mã OTP (6 số)" maxlength="6">
                        <button type="button" class="verify-button" onclick="verifyOTP()">Xác nhận</button>
                    </div>
                    <div class="success-message" id="otpSuccess">✓ Xác thực thành công!</div>
                    <div class="error-message" id="otpError">Mã OTP không đúng!</div>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                    <span class="toggle-password" onclick="togglePassword('password')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </span>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Nhập lại mật khẩu</label>
                <div class="password-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Nhập lại mật khẩu" required>
                    <span class="toggle-password" onclick="togglePassword('confirmPassword')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </span>
                </div>
                <div class="error-message" id="passwordError">Mật khẩu không khớp!</div>
            </div>

            <div class="captcha-container">
                <div class="captcha-box">
                    <canvas id="captchaCanvas" width="200" height="60"></canvas>
                    <button type="button" class="refresh-captcha" onclick="generateCaptcha()">⟳</button>
                </div>
                <input type="text" id="captchaInput" class="captcha-input" placeholder="Nhập mã xác nhận" required>
                <div class="error-message" id="captchaError">Mã xác nhận không đúng!</div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms" class="checkbox-label">
                    Khi tạo tài khoản hoặc tiếp tục sử dụng ứng dụng, website hoặc phần mềm của Sweepsouth, bạn xác nhận rằng bạn đã đồng ý với <a href="terms.html">Điều khoản dịch vụ</a> và đã xem xét <a href="privacy.html">Chính sách bảo mật</a>.
                </label>
            </div>
            <div class="error-message" id="termsError">Vui lòng tích vào ô đồng ý điều khoản để tiếp tục đăng ký.</div>

            <button type="submit" class="register-button">Đăng ký</button>

            <div class="login-link">
                Đã có tài khoản? <a href="{{ url('/login') }}">Đăng nhập ngay</a>
            </div>
        </form>
    </div>

    <script>
        let generatedOTP = '';
        let otpVerified = false;
        let countdownInterval;
        let captchaText = '';

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleIcon = field.parentElement.querySelector('.toggle-password');
            
            if (field.type === 'password') {
                field.type = 'text';
                toggleIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                </svg>`;
            } else {
                field.type = 'password';
                toggleIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>`;
            }
        }

        function generateCaptcha() {
            const canvas = document.getElementById('captchaCanvas');
            const ctx = canvas.getContext('2d');
            
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            captchaText = '';
            for (let i = 0; i < 6; i++) {
                captchaText += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            for (let i = 0; i < 50; i++) {
                ctx.strokeStyle = `rgba(150, 150, 150, 0.2)`;
                ctx.beginPath();
                ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
                ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
                ctx.stroke();
            }
            
            ctx.font = 'bold 32px Arial';
            ctx.fillStyle = '#004d2e';
            ctx.textBaseline = 'middle';
            
            for (let i = 0; i < captchaText.length; i++) {
                ctx.save();
                const x = 20 + i * 30;
                const y = canvas.height / 2;
                ctx.translate(x, y);
                ctx.rotate((Math.random() - 0.5) * 0.4);
                ctx.fillText(captchaText[i], 0, 0);
                ctx.restore();
            }
            
            document.getElementById('captchaInput').value = '';
            document.getElementById('captchaError').style.display = 'none';
        }

        function sendOTP() {
            const phone = document.getElementById('phone').value;
            
            if (!phone || phone.length < 10) {
                alert('Vui lòng nhập số điện thoại hợp lệ!');
                return;
            }

            generatedOTP = Math.floor(100000 + Math.random() * 900000).toString();
            console.log('Mã OTP của bạn là: ' + generatedOTP);
            
            document.getElementById('otpVerification').classList.add('show');
            
            const sendBtn = document.getElementById('sendOtpBtn');
            sendBtn.disabled = true;
            
            let timeLeft = 60;
            const timerElement = document.getElementById('timer');
            
            countdownInterval = setInterval(() => {
                timeLeft--;
                timerElement.textContent = `Gửi lại sau ${timeLeft}s`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    sendBtn.disabled = false;
                    timerElement.textContent = '';
                }
            }, 1000);

            alert('Mã OTP đã được gửi đến số điện thoại của bạn!\n(Demo: ' + generatedOTP + ')');
        }

        function verifyOTP() {
            const inputOTP = document.getElementById('otpCode').value;
            const otpError = document.getElementById('otpError');
            const otpSuccess = document.getElementById('otpSuccess');
            
            if (inputOTP === generatedOTP) {
                otpVerified = true;
                otpSuccess.classList.add('show');
                otpError.style.display = 'none';
                document.getElementById('otpCode').disabled = true;
                document.querySelector('.verify-button').disabled = true;
                document.querySelector('.verify-button').textContent = '✓ Đã xác thực';
            } else {
                otpError.style.display = 'block';
                otpSuccess.classList.remove('show');
            }
        }

        function checkPasswordStrength(password) {
            const strengthElement = document.getElementById('passwordStrength');
            let strength = 0;

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;

            strengthElement.className = 'password-strength';
            if (password.length === 0) {
                strengthElement.style.display = 'none';
                return;
            }

            if (strength <= 1) {
                strengthElement.classList.add('weak');
                strengthElement.textContent = '⚠ Mật khẩu yếu';
            } else if (strength <= 3) {
                strengthElement.classList.add('medium');
                strengthElement.textContent = '✓ Mật khẩu trung bình';
            } else {
                strengthElement.classList.add('strong');
                strengthElement.textContent = '✓ Mật khẩu mạnh';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorElement = document.getElementById('passwordError');

            if (confirmPassword.length > 0 && password !== confirmPassword) {
                errorElement.style.display = 'block';
                return false;
            } else {
                errorElement.style.display = 'none';
                return true;
            }
        }

        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });

        document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);

        document.getElementById('terms').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('termsError').style.display = 'none';
            }
        });

        document.getElementById('otpCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                verifyOTP();
            }
        });

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const termsCheckbox = document.getElementById('terms');
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const captchaInput = document.getElementById('captchaInput').value;

            if (!otpVerified) {
                alert('Vui lòng xác thực số điện thoại qua OTP!');
                isValid = false;
            }

            if (!termsCheckbox.checked) {
                document.getElementById('termsError').style.display = 'block';
                isValid = false;
            }

            if (password !== confirmPassword) {
                document.getElementById('passwordError').style.display = 'block';
                isValid = false;
            }

            if (captchaInput !== captchaText) {
                document.getElementById('captchaError').style.display = 'block';
                generateCaptcha();
                isValid = false;
            } else {
                document.getElementById('captchaError').style.display = 'none';
            }

            if (!isValid) {
                return false;
            }
            
            alert('Đăng ký thành công!');
        });

        window.addEventListener('load', generateCaptcha);
    </script>
</body>
</html>
