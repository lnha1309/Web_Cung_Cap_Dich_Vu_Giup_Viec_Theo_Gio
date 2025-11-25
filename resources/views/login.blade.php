<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
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
            margin-bottom: 30px;
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
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #004d2e;
        }

        .login-button {
            width: 100%;
            padding: 15px;
            background: #004d2e;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .login-button:hover {
            background: #003d23;
            transform: translateY(-2px);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .signup-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 15px;
        }

        .signup-link a {
            color: #004d2e;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #003d23;
            text-decoration: underline;
        }

        .forgot-password {
            margin-top: 10px;
        }

        .link-button {
            background: none;
            border: none;
            color: #004d2e;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }

        .forgot-panel {
            margin-top: 12px;
            padding: 14px;
            border: 1px solid #e6e6e6;
            border-radius: 10px;
            background: #fafafa;
            display: none;
        }

        .forgot-hint {
            display: flex;
            gap: 8px;
            align-items: center;
            background: #fff4e6;
            border: 1px solid #ffd8a8;
            color: #b35a00;
            padding: 10px 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .inline-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .inline-actions input {
            flex: 1;
        }

        .otp-button,
        .reset-button {
            background: #004d2e;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
            cursor: pointer;
            min-width: 120px;
            transition: all 0.2s ease;
        }

        .otp-button:hover,
        .reset-button:hover {
            background: #003d23;
            transform: translateY(-1px);
        }

        .otp-button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .status-text {
            margin-top: 8px;
            font-size: 14px;
            color: #c0392b;
        }

        .status-text.success {
            color: #0b8a3c;
        }

        .small-note {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-wrapper">
            <a href="{{ url('/') }}">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo">
            </a>
        </div>
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <h1>Đăng nhập</h1>

            @if ($errors->has('login'))
                <div style="color: red; margin-bottom: 15px;">
                    {{ $errors->first('login') }}
                </div>
            @endif

            <div class="form-group">
                <label for="TenDN">Tên đăng nhập</label>
                <input
                    type="text"
                    id="TenDN"
                    name="TenDN"
                    value="{{ old('TenDN') }}"
                    placeholder="Nhập tên đăng nhập của bạn"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Nhập mật khẩu"
                    required
                >
            </div>
                        <div class="forgot-password">
                <div id="failedHint" class="forgot-hint" style="display: {{ !empty($suggestReset) ? 'block' : 'none' }};">
                    <span>Bạn đã nhập sai mật khẩu 3 lần <strong>{{ $suggestReset }}</strong>.</span>
                    <button type="button" class="link-button" id="openForgotFromHint">Lấy lại mật khẩu</button>
                </div>
                <button type="button" class="link-button" id="toggleForgot">Quên mật khẩu?</button>
                <div id="forgotPanel" class="forgot-panel">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="forgotUsername">Tên tài khoản</label>
                        <input type="text" id="forgotUsername" placeholder="Nhập tên tài khoản">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="forgotEmail">Email</label>
                        <div class="inline-actions">
                            <input type="email" id="forgotEmail" placeholder="Nhập email đã đăng ký">
                            <button type="button" class="otp-button" id="sendOtpBtn">Gửi OTP</button>
                        </div>
                        <div class="small-note">Mã OTP đã gửi cho bạn.</div>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="otpCode">Mã OTP</label>
                        <input type="text" id="otpCode" placeholder="Nhập mã OTP" maxlength="6">
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="newPassword">Mật khẩu mới</label>
                        <input type="password" id="newPassword" placeholder="Nhập mật khẩu mới" minlength="6">
                    </div>

                    <div>
                        <button type="button" class="reset-button" id="resetPwdBtn">Đổi mật khẩu</button>
                        <div id="forgotStatus" class="status-text"></div>
                    </div>
                </div>
            </div>



            <button type="submit" class="login-button">Đăng nhập</button>

            <div class="signup-link">
                Bạn chưa có tài khoản? <a href="{{ url('register') }}">Đăng ký ngay</a>
            </div>
        </form>
    </div>
        <script>
        (function() {
            var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
            var toggleForgot = document.getElementById('toggleForgot');
            var openForgotFromHint = document.getElementById('openForgotFromHint');
            var forgotPanel = document.getElementById('forgotPanel');
            var forgotStatus = document.getElementById('forgotStatus');
            var sendOtpBtn = document.getElementById('sendOtpBtn');
            var resetPwdBtn = document.getElementById('resetPwdBtn');
            var forgotEmailInput = document.getElementById('forgotEmail');
            var forgotUsernameInput = document.getElementById('forgotUsername');
            var otpInput = document.getElementById('otpCode');
            var newPasswordInput = document.getElementById('newPassword');
            var loginPasswordInput = document.getElementById('password');
            var loginUsernameInput = document.getElementById('TenDN');
            var suggestedUsername = @json($suggestReset ?? '');

            function setForgotStatus(message, type) {
                if (!forgotStatus) return;
                forgotStatus.textContent = message || '';
                forgotStatus.classList.remove('success');
                if (type === 'success') {
                    forgotStatus.classList.add('success');
                }
            }

            function openForgotPanel(prefillUsername) {
                if (!forgotPanel) return;
                forgotPanel.style.display = 'block';
                var usernameValue = prefillUsername || (loginUsernameInput ? loginUsernameInput.value : '');
                if (forgotUsernameInput && usernameValue) {
                    forgotUsernameInput.value = usernameValue;
                }
                if (forgotEmailInput) {
                    forgotEmailInput.focus();
                }
                setForgotStatus('');
            }

            if (toggleForgot) {
                toggleForgot.addEventListener('click', function() {
                    var isOpen = forgotPanel && forgotPanel.style.display === 'block';
                    if (isOpen) {
                        forgotPanel.style.display = 'none';
                    } else {
                        openForgotPanel();
                    }
                });
            }

            if (openForgotFromHint) {
                openForgotFromHint.addEventListener('click', function() {
                    openForgotPanel(suggestedUsername || (loginUsernameInput ? loginUsernameInput.value : ''));
                });
            }

            if (sendOtpBtn) {
                sendOtpBtn.addEventListener('click', function() {
                    var email = forgotEmailInput ? (forgotEmailInput.value || '').trim() : '';
                    var username = forgotUsernameInput ? (forgotUsernameInput.value || '').trim() : '';
                    if (!username && loginUsernameInput) {
                        username = (loginUsernameInput.value || '').trim();
                    }

                    setForgotStatus('');
                    if (!username || !email) {
                        setForgotStatus('Vui lòng nhập tên đăng nhập mật khẩu.');
                        return;
                    }

                    sendOtpBtn.disabled = true;
                    fetch("{{ route('password.sendOtp') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ username: username, email: email }),
                    })
                        .then(function(res) {
                            return res.json().then(function(data) {
                                return { ok: res.ok, data: data };
                            });
                        })
                        .then(function(result) {
                            if (result.ok) {
                                setForgotStatus(result.data.message || 'Đã gửi mã OTP.', 'success');
                                if (otpInput) {
                                    otpInput.focus();
                                }
                            } else {
                                setForgotStatus(result.data.message || 'Không gửi được mã, vui lòng thử lại sau.');
                            }
                        })
                        .catch(function() {
                            setForgotStatus('Không gửi được mã, vui lòng thử lại sau.');
                        })
                        .finally(function() {
                            sendOtpBtn.disabled = false;
                        });
                });
            }

            if (resetPwdBtn) {
                resetPwdBtn.addEventListener('click', function() {
                    var email = forgotEmailInput ? (forgotEmailInput.value || '').trim() : '';
                    var username = forgotUsernameInput ? (forgotUsernameInput.value || '').trim() : '';
                    if (!username && loginUsernameInput) {
                        username = (loginUsernameInput.value || '').trim();
                    }
                    var otp = otpInput ? (otpInput.value || '').trim() : '';
                    var newPassword = newPasswordInput ? newPasswordInput.value || '' : '';

                    setForgotStatus('');

                    if (!username || !email || !otp || !newPassword) {
                        setForgotStatus('Vui lòng đăng nhập bằng tài khoản mật khẩu mới.');
                        return;
                    }

                    resetPwdBtn.disabled = true;
                    fetch("{{ route('password.resetWithOtp') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            username: username,
                            email: email,
                            otp: otp,
                            password: newPassword,
                        }),
                    })
                        .then(function(res) {
                            return res.json().then(function(data) {
                                return { ok: res.ok, data: data };
                            });
                        })
                        .then(function(result) {
                            if (result.ok) {
                                setForgotStatus(result.data.message || 'Đổi mật khẩu thành công.', 'success');
                                if (loginPasswordInput) {
                                    loginPasswordInput.value = newPassword;
                                }
                            } else {
                                setForgotStatus(result.data.message || 'Không thể đổi mật khẩu, vui lòng thử lại sau.');
                            }
                        })
                        .catch(function() {
                            setForgotStatus('Không thể đổi mật khẩu, vui lòng thử lại sau.');
                        })
                        .finally(function() {
                            resetPwdBtn.disabled = false;
                        });
                });
            }
        })();
    </script>

</body>
</html>

