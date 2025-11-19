<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5efe7;
            color: #333;
            margin: 0;
        }
        .header {
            background-color: white;
            padding: 10px 40px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .status-success {
            color: #2e7d32;
        }
        .status-failed {
            color: #c62828;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            margin-top: 16px;
        }
        .btn-primary {
            background-color: #004d2e;
            color: #fff;
        }
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .result-modal {
            background: #fff;
            border-radius: 12px;
            padding: 24px 28px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }
        .result-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        .result-icon.success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .result-icon.failed {
            background: #ffebee;
            color: #c62828;
        }
        .result-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .result-text {
            font-size: 14px;
            color: #555;
            margin-bottom: 4px;
        }
        .result-note {
            font-size: 13px;
            color: #777;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        <strong>Dọn Dẹp Nhà Cửa</strong>
    </div>
    <div>
        <a href="{{ url('/') }}">Quay về trang chủ</a>
    </div>
</div>

<div class="container">
    <h2>Kết quả thanh toán</h2>
    <p class="{{ $status === 'success' ? 'status-success' : 'status-failed' }}">
        {{ $message }}
    </p>

    @if($orderId)
        <p>Mã đơn hàng: <strong>{{ $orderId }}</strong></p>
    @endif

    @if($transactionNo)
        <p>Mã giao dịch VNPAY: <strong>{{ $transactionNo }}</strong></p>
    @endif

    @if($responseCode)
        <p>Mã phản hồi: <strong>{{ $responseCode }}</strong></p>
    @endif

    <a href="{{ route('booking.show') }}" class="btn btn-primary">Quay lại đặt dịch vụ</a>
</div>

@if($status === 'success')
    <div class="overlay" id="paymentSuccessOverlay">
        <div class="result-modal">
            <div class="result-icon success">&#10003;</div>
            <div class="result-title">Thanh toán thành công</div>
            <p class="result-text">Cảm ơn bạn đã sử dụng dịch vụ.</p>
            <p class="result-note">Tự động quay về trang chủ sau <span id="paymentCountdown">5</span> giây.</p>
        </div>
    </div>

    <script>
        (function () {
            const countdownEl = document.getElementById('paymentCountdown');
            const homeUrl = '{{ url('/') }}';
            let seconds = 5;

            if (countdownEl) {
                countdownEl.textContent = String(seconds);
            }

            const intervalId = setInterval(function () {
                seconds -= 1;
                if (countdownEl && seconds >= 0) {
                    countdownEl.textContent = String(seconds);
                }
                if (seconds <= 0) {
                    clearInterval(intervalId);
                }
            }, 1000);

            setTimeout(function () {
                window.location.href = homeUrl;
            }, seconds * 1000);
        })();
    </script>
@endif

</body>
</html>

