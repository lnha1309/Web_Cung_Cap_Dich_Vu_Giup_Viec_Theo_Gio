<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo phân công làm việc</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f8fb; padding: 24px; color: #222;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.06);">
        <tr>
            <td style="padding: 20px 24px; background: #004d2e; color: #fff;">
                <h2 style="margin: 0; font-size: 20px;">Thông báo phân công làm việc</h2>
                <p style="margin: 4px 0 0 0; font-size: 14px; opacity: 0.85;">Mã đơn: <strong>{{ $order_id }}</strong></p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin-top: 0;">Chào {{ $staff_name }},</p>
                <p style="line-height: 1.5; margin-bottom: 16px;">
                    Bạn đã được phân công làm việc cho đơn hàng theo tháng. Dưới đây là thông tin chi tiết ca làm việc của bạn.
                </p>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 18px;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; width: 160px;">Dịch vụ</td>
                        <td style="padding: 8px 0;">{{ $service_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Khách hàng</td>
                        <td style="padding: 8px 0;">{{ $customer_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Ngày làm việc</td>
                        <td style="padding: 8px 0;">{{ $session_date }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Giờ làm việc</td>
                        <td style="padding: 8px 0;">{{ $session_time }}</td>
                    </tr>
                    @if($address)
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Địa chỉ</td>
                            <td style="padding: 8px 0;">{{ $address }}</td>
                        </tr>
                    @endif
                    @if(isset($customer_phone))
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold;">SĐT khách hàng</td>
                            <td style="padding: 8px 0;">{{ $customer_phone }}</td>
                        </tr>
                    @endif
                </table>

                <p style="margin: 0; padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <strong>Lưu ý:</strong> Vui lòng chuẩn bị và có mặt đúng giờ. Nếu có bất kỳ vấn đề gì, hãy liên hệ với quản lý ngay lập tức.
                </p>
            </td>
        </tr>
        <tr>
            <td style="background: #f3f6fb; padding: 14px 24px; font-size: 12px; color: #555;">
                Email này được gửi từ hệ thống Dọn Dẹp Nhà Cửa. Vui lòng không trả lời trực tiếp email này.
            </td>
        </tr>
    </table>
</body>
</html>
