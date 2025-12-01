<!DOCTYPE html>
<html>
<head>
    <title>Thông báo hủy buổi làm việc</title>
</head>
<body>
    <p>Xin chào <strong>{{ $staff_name }}</strong>,</p>
    
    <p>Chúng tôi xin thông báo buổi làm việc của bạn đã bị hủy bởi khách hàng.</p>
    
    <p><strong>Chi tiết buổi làm:</strong></p>
    <ul>
        <li>Mã đơn hàng: #{{ $order_id }}</li>
        <li>Ngày làm: {{ $session_date }}</li>
        <li>Thời gian: {{ $session_time }}</li>
    </ul>

    <p>Lý do hủy: {{ $reason ?? 'Khách hàng hủy' }}</p>

    <p>Lịch làm việc của bạn cho khung giờ này đã được cập nhật lại trạng thái sẵn sàng.</p>

    <p>Trân trọng,<br>Đội ngũ quản lý.</p>
</body>
</html>
