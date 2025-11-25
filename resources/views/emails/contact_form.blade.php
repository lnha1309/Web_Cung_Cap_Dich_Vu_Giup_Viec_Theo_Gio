<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin liên hệ mới</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111; line-height: 1.6;">
    <h2>Thông tin liên hệ mới từ khách hàng</h2>

    <p><strong>Họ và tên:</strong> {{ $data['name'] }}</p>
    <p><strong>Số điện thoại:</strong> {{ $data['phone'] }}</p>
    <p><strong>Email:</strong> {{ $data['email'] }}</p>

    <p><strong>Nội dung:</strong></p>
    <p style="white-space: pre-line; margin: 0;">{{ $data['message'] }}</p>
</body>
</html>
