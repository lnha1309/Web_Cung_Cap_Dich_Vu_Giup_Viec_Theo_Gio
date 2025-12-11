<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - OTP qua Email</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background-color: #f9f9f9; /* Added background color for better contrast */
        }
        .register-container { 
            width: 100%; 
            max-width: 500px; /* Slightly increased max-width */
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .logo-link {
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }
        .register-logo {
            height: 60px;
            width: auto;
            object-fit: contain;
            transition: transform 0.2s ease;
        }
        .register-logo:hover {
            transform: scale(1.02);
        }
        h1 { text-align: center; margin-bottom: 30px; color: #333; font-size: 28px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; }
        input { 
            width: 100%; 
            padding: 14px; 
            border: 2px solid #e1e1e1; 
            border-radius: 10px; 
            font-size: 15px; 
            transition: border-color 0.3s;
            box-sizing: border-box; /* Ensure padding doesn't affect width */
        }
        input:focus { border-color: #004d2e; outline: none; }

        .otp-group { display: flex; gap: 10px; align-items: center; }
        .otp-button, .verify-button {
            background: #004d2e; color: white; border: none; border-radius: 8px;
            padding: 12px 20px; cursor: pointer; transition: 0.3s; font-weight: 600;
            white-space: nowrap; /* Prevent text wrapping */
        }
        .otp-button:hover, .verify-button:hover { background: #003d23; }
        .otp-button:disabled { background: #ccc; cursor: not-allowed; }

        .otp-verification {
            display: none; margin-top: 15px; padding: 20px;
            background: #f0f8f5; border: 2px solid #004d2e; border-radius: 12px;
        }
        .otp-verification.show { display: block; }
        .success-message { color: #4caf50; margin-top: 8px; display: none; }
        .error-message { color: #d32f2f; margin-top: 8px; display: none; font-size: 13px; }
        input.invalid { border-color: #d32f2f; }

        .register-button {
            width: 100%; padding: 14px; background: #004d2e;
            color: white; font-size: 18px; border: none; border-radius: 10px;
            cursor: pointer; transition: 0.3s; font-weight: bold;
            margin-top: 10px;
        }
        .register-button:hover { background: #003d23; }

        .alert {
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-error {
            background-color: #fdecea;
            color: #b71c1c;
            border: 1px solid #f5c6cb;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .register-container {
                padding: 30px 20px;
                max-width: 100%;
            }
            h1 { font-size: 24px; margin-bottom: 25px; }
        }

        @media (max-width: 480px) {
            body { padding: 15px; }
            .register-container { padding: 25px 15px; }
            h1 { font-size: 22px; margin-bottom: 20px; }
            
            .otp-group {
                flex-direction: column;
                align-items: stretch;
            }
            .otp-button { width: 100%; }
            
            .otp-input-group {
                flex-direction: column;
            }
            .verify-button { width: 100%; margin-top: 10px; }
            
            input { padding: 12px; font-size: 14px; }
            .register-button { padding: 12px; font-size: 16px; }
        }
    </style>
</head>
<body>
<div class="register-container">
    <a href="{{ url('/') }}" class="logo-link" aria-label="Quay ve trang chu">
        <img src="{{ asset('assets/logo.png') }}" alt="Trang chu" class="register-logo">
    </a>
    <h1>Đăng ký tài khoản</h1>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="registerForm" method="POST" action="{{ route('register.post') }}">
        @csrf

        <div class="form-group">
            <label for="username">Tên đăng nhập</label>
            <input type="text" id="username" name="TenDN" value="{{ old('TenDN') }}" required>
            <div class="error-message" id="usernameError"></div>
        </div>

        <div class="form-group">
            <label for="fullname">Họ và tên</label>
            <input type="text" id="fullname" name="Ten_KH" value="{{ old('Ten_KH') }}" required>
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="SDT" value="{{ old('SDT') }}" required>
            <div class="error-message" id="phoneError"></div>
        </div>

        <div class="form-group">
            <label for="email">Email xác thực</label>
            <div class="otp-group">
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Nhập email của bạn" required>
                <button type="button" id="sendOtpBtn" class="otp-button" onclick="sendEmailOTP()">Gửi OTP</button>
            </div>
            <div class="timer" id="timer" style="font-size:13px; color:#555; margin-top:5px;"></div>

            <div class="otp-verification" id="otpVerification">
                <div class="otp-info">Mã OTP đã được gửi đến email của bạn.</div>
                <div class="otp-input-group" style="margin-top:10px; display:flex; gap:10px;">
                    <input type="text" id="otpCode" name="otp" placeholder="Nhập mã OTP (6 số)" maxlength="6" class="otp-input">
                    <button type="button" class="verify-button" onclick="verifyOTP()">Xác nhận</button>
                </div>
                <div class="success-message" id="otpSuccess">Đã xác thực thành công!</div>
                <div class="error-message" id="otpError">Mã OTP không đúng hoặc đã hết hạn!</div>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
            <div class="error-message" id="passwordRuleError">Mật khẩu phải có 8 ký tự, gồm chữ hoa, chữ thường và ký tự đặc biệt.</div>
        </div>

        <div class="form-group">
            <label for="confirmPassword">Nhập lại mật khẩu</label>
            <input type="password" id="confirmPassword" name="password_confirmation" required>
            <div class="error-message" id="passwordError">Mật khẩu không khớp!</div>
        </div>

        <button type="submit" class="register-button">Đăng ký</button>
    </form>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';
    let countdown = null;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/;
    const phoneRegex = /^(0|\+84)[3|5|7|8|9][0-9]{8}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const usernameInput = document.getElementById('username');
    const usernameError = document.getElementById('usernameError');
    const phoneInput = document.getElementById('phone');
    const phoneError = document.getElementById('phoneError');

    function sendEmailOTP() {
        const emailInput = document.getElementById('email');
        const email = emailInput.value.trim();
        if (!emailRegex.test(email)) {
            emailInput.classList.add('invalid');
            alert('Vui lòng nhập email hợp lệ!');
            return;
        }
        emailInput.classList.remove('invalid');

        const sendBtn = document.getElementById('sendOtpBtn');
        sendBtn.disabled = true;

        fetch('{{ route('register.sendOtp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email }),
        })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const errors = data.errors || {};
                    const firstError = Object.values(errors)[0]?.[0] ?? data.message ?? 'Không gửi được OTP. Vui lòng thử lại.';
                    throw new Error(firstError);
                }

                document.getElementById('otpVerification').classList.add('show');
                startOtpCountdown();
                alert('Mã OTP đã được gửi đến email của bạn!');
            })
            .catch(error => {
                alert(error.message || 'Không gửi được OTP. Vui lòng thử lại.');
                sendBtn.disabled = false;
            });
    }

    function startOtpCountdown() {
        const sendBtn = document.getElementById('sendOtpBtn');
        let timeLeft = 60;
        const timerElement = document.getElementById('timer');

        if (countdown) {
            clearInterval(countdown);
        }

        timerElement.textContent = `Gửi lại sau ${timeLeft}s`;

        countdown = setInterval(() => {
            timeLeft--;
            timerElement.textContent = `Gửi lại sau ${timeLeft}s`;
            if (timeLeft <= 0) {
                clearInterval(countdown);
                sendBtn.disabled = false;
                timerElement.textContent = '';
            }
        }, 1000);
    }

    function verifyOTP() {
        const emailInput = document.getElementById('email');
        const email = emailInput.value.trim();
        const otp = document.getElementById('otpCode').value.trim();
        const otpError = document.getElementById('otpError');
        const otpSuccess = document.getElementById('otpSuccess');

        if (!email || !otp) {
            alert('Vui lòng nhập email và mã OTP.');
            return;
        }

        fetch('{{ route('register.verifyOtp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ email, otp }),
        })
            .then(async response => {
                const data = await response.json();
                if (!response.ok || !data.valid) {
                    throw new Error(data.message || 'OTP không hợp lệ');
                }
                otpSuccess.style.display = 'block';
                otpError.style.display = 'none';
                document.getElementById('otpCode').disabled = true;
                emailInput.readOnly = true;
                document.getElementById('sendOtpBtn').disabled = true;
            })
            .catch(error => {
                otpError.textContent = error.message || 'Mã OTP không đúng hoặc đã hết hạn!';
                otpError.style.display = 'block';
                otpSuccess.style.display = 'none';
            });
    }

    function checkPasswordMatch() {
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirmPassword').value;
        const error = document.getElementById('passwordError');
        const ruleError = document.getElementById('passwordRuleError');

        const strong = passwordRegex.test(pass);
        if (pass && !strong) {
            ruleError.style.display = 'block';
            document.getElementById('password').classList.add('invalid');
        } else {
            ruleError.style.display = 'none';
            document.getElementById('password').classList.remove('invalid');
        }

        if (confirm.length > 0 && pass !== confirm) {
            error.style.display = 'block';
            document.getElementById('confirmPassword').classList.add('invalid');
            return false;
        } else {
            error.style.display = 'none';
            document.getElementById('confirmPassword').classList.remove('invalid');
        }

        return strong;
    }

    usernameInput.addEventListener('blur', () => {
        const TenDN = usernameInput.value.trim();
        if (!TenDN) {
            usernameError.style.display = 'none';
            usernameInput.classList.remove('invalid');
            return;
        }

        if (TenDN.length < 4) {
            usernameError.textContent = 'Tên đăng nhập tối thiểu 4 ký tự.';
            usernameError.style.display = 'block';
            usernameInput.classList.add('invalid');
            return;
        }

        fetch('{{ route('register.checkUsername') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ TenDN }),
        })
            .then(res => res.json())
            .then(data => {
                if (!data.available) {
                    usernameError.textContent = 'Tên đăng nhập đã tồn tại, vui lòng chọn tên khác.';
                    usernameError.style.display = 'block';
                    usernameInput.classList.add('invalid');
                } else {
                    usernameError.style.display = 'none';
                    usernameInput.classList.remove('invalid');
                }
            })
            .catch(() => {
                usernameError.textContent = '';
                usernameError.style.display = 'none';
                usernameInput.classList.remove('invalid');
            });
    });

    phoneInput.addEventListener('blur', () => {
        const SDT = phoneInput.value.trim();
        if (!SDT) {
            phoneError.style.display = 'none';
            phoneInput.classList.remove('invalid');
            return;
        }

        if (!phoneRegex.test(SDT)) {
            phoneError.textContent = 'Số điện thoại không hợp lệ.';
            phoneError.style.display = 'block';
            phoneInput.classList.add('invalid');
            return;
        }

        fetch('{{ route('register.checkPhone') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ SDT }),
        })
            .then(res => res.json())
            .then(data => {
                if (!data.available) {
                    phoneError.textContent = 'Số điện thoại đã được sử dụng, vui lòng nhập số khác.';
                    phoneError.style.display = 'block';
                    phoneInput.classList.add('invalid');
                } else {
                    phoneError.style.display = 'none';
                    phoneInput.classList.remove('invalid');
                }
            })
            .catch(() => {
                phoneError.textContent = '';
                phoneError.style.display = 'none';
                phoneInput.classList.remove('invalid');
            });
    });

    document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
    document.getElementById('password').addEventListener('input', checkPasswordMatch);
    phoneInput.addEventListener('input', () => {
        phoneError.style.display = 'none';
        phoneInput.classList.remove('invalid');
    });
    usernameInput.addEventListener('input', () => {
        usernameError.style.display = 'none';
        usernameInput.classList.remove('invalid');
    });

    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const usernameVal = usernameInput.value.trim();
        const phoneVal = phoneInput.value.trim();
        const passOk = checkPasswordMatch();

        if (usernameVal.length < 4) {
            usernameError.textContent = 'Tên đăng nhập tối thiểu 4 ký tự.';
            usernameError.style.display = 'block';
            usernameInput.classList.add('invalid');
            e.preventDefault();
            return;
        }

        if (!phoneRegex.test(phoneVal)) {
            phoneError.textContent = 'Số điện thoại không hợp lệ.';
            phoneError.style.display = 'block';
            phoneInput.classList.add('invalid');
            e.preventDefault();
            return;
        }

        if (!passOk || document.getElementById('passwordError').style.display === 'block' || document.getElementById('passwordRuleError').style.display === 'block') {
            e.preventDefault();
            alert('Mật khẩu chưa đạt yêu cầu.');
            return;
        }

        if (usernameError.style.display === 'block' || phoneError.style.display === 'block') {
            e.preventDefault();
            alert('Vui lòng sửa lỗi ở tên đăng nhập / số điện thoại.');
            return;
        }
    });
</script>
</body>
</html>
