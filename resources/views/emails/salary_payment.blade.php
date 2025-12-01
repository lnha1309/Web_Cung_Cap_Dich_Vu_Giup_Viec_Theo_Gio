<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo thanh toán lương</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f8fb; padding: 24px; color: #222;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.06);">
        <tr>
            <td style="padding: 20px 24px; background: #FF7B29; color: #fff;">
                <h2 style="margin: 0; font-size: 20px;">
                    💰 Thông báo thanh toán lương
                </h2>
                <p style="margin: 4px 0 0 0; font-size: 14px; opacity: 0.85;">Mã giao dịch: <strong>{{ $transaction_id }}</strong></p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin-top: 0;">Chào <strong>{{ $employee_name }}</strong>,</p>
                <p style="line-height: 1.5; margin-bottom: 16px;">
                    Chúng tôi xin thông báo rằng lương của bạn đã được thanh toán thành công. Dưới đây là thông tin chi tiết:
                </p>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 18px; background: #f9fafb; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="padding: 12px 16px; font-weight: bold; width: 180px; border-bottom: 1px solid #e5e7eb;">Mã nhân viên</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #e5e7eb;">{{ $employee_id }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-weight: bold;">Ngày thanh toán</td>
                        <td style="padding: 12px 16px;">{{ $payment_date }}</td>
                    </tr>
                </table>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 18px; background: #FEF3C7; border-radius: 8px; overflow: hidden;">
                    <tr>
                        <td style="padding: 12px 16px; font-weight: bold; width: 180px; border-bottom: 1px solid #FDE68A;">Số dư trước</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #FDE68A; font-size: 15px;">{{ number_format($balance_before) }} đ</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-weight: bold; border-bottom: 1px solid #FDE68A;">Số tiền lương</td>
                        <td style="padding: 12px 16px; border-bottom: 1px solid #FDE68A; font-size: 18px; font-weight: bold; color: #15803D;">{{ number_format($salary_amount) }} đ</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 16px; font-weight: bold;">Số dư sau</td>
                        <td style="padding: 12px 16px; font-size: 15px;">{{ number_format($balance_after) }} đ</td>
                    </tr>
                </table>

                <div style="background: #E0F2FE; padding: 14px 16px; border-radius: 8px; border-left: 4px solid #0369A1; margin-bottom: 16px;">
                    <p style="margin: 0; font-size: 14px; color: #0369A1;">
                        <strong>Lưu ý:</strong> Số tiền lương đã được chuyển vào tài khoản bạn đăng ký. Vui lòng kiểm tra và xác nhận.
                    </p>
                </div>

                <p style="margin: 16px 0 0 0; line-height: 1.5;">
                    Cảm ơn bạn đã cống hiến và làm việc chăm chỉ. Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với bộ phận quản lý.
                </p>

                <p style="margin: 16px 0 0 0;">
                    Trân trọng,<br>
                    <strong>Đội ngũ Quản lý</strong>
                </p>
            </td>
        </tr>
        <tr>
            <td style="background: #f3f6fb; padding: 14px 24px; font-size: 12px; color: #555;">
                Email này được gửi tự động từ hệ thống quản lý. Vui lòng không trả lời trực tiếp email này.
            </td>
        </tr>
    </table>
</body>
</html>
