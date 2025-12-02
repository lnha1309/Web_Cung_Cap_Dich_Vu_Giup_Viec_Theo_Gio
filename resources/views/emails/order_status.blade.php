<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo đơn hàng</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f6f8fb; padding: 24px; color: #222;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.06);">
        <tr>
            <td style="padding: 20px 24px; background: #004d2e; color: #fff;">
                <h2 style="margin: 0; font-size: 20px;">
                    @if($type === 'created')
                        Xác nhận đặt đơn thành công
                    @elseif($type === 'failed')
                        Thanh toán không thành công
                    @elseif($type === 'session_cancelled')
                        Hủy buổi làm việc
                    @else
                        Đơn hàng đã bị hủy
                    @endif
                </h2>
                <p style="margin: 4px 0 0 0; font-size: 14px; opacity: 0.85;">Mã đơn: <strong>{{ $booking->ID_DD }}</strong></p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin-top: 0;">Chào {{ $customer_name }},</p>
                <p style="line-height: 1.5; margin-bottom: 16px;">
                    @if($type === 'created')
                        Cảm ơn bạn đã đặt dịch vụ. Dưới đây là thông tin chi tiết đơn hàng của bạn.
                    @elseif($type === 'failed')
                        Việc thanh toán qua VNPay không thành công. Bạn vui lòng kiểm tra lại và đặt lại đơn nếu cần.
                    @elseif($type === 'session_cancelled')
                        Buổi làm việc của bạn đã được hủy. {{ $reason ?? 'Không xác định' }}.
                    @else
                        Đơn hàng của bạn đã được hủy. Lý do: {{ $reason ?? 'Không xác định' }}.
                    @endif
                </p>

                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 18px;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; width: 160px;">Dịch vụ</td>
                        <td style="padding: 8px 0;">{{ $service_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Thời gian</td>
                        <td style="padding: 8px 0;">{{ $start_time }}</td>
                    </tr>
                    @if($address)
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Địa chỉ</td>
                            <td style="padding: 8px 0;">{{ $address }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">
                            @if($type === 'session_cancelled')
                                Giá trị buổi làm
                            @else
                                Số tiền
                            @endif
                        </td>
                        <td style="padding: 8px 0;">{{ number_format((float) $amount, 0, ',', '.') }} đ</td>
                    </tr>
                    @if($type === 'failed' && $reason)
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold;">Lý do</td>
                            <td style="padding: 8px 0;">{{ $reason }}</td>
                        </tr>
                    @endif
                    @if($type === 'session_cancelled' && ($refund_amount ?? 0) > 0)
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold;">Số tiền hoàn (80%)</td>
                            <td style="padding: 8px 0; color: #d9534f; font-weight: bold;">{{ number_format((float) $refund_amount, 0, ',', '.') }} đ ({{ $payment_method ?? 'VNPay' }})</td>
                        </tr>
                    @endif
                    @if(($type === 'cancelled' || $type === 'failed') && ($refund_amount ?? 0) > 0)
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold;">Hoàn tiền</td>
                            <td style="padding: 8px 0;">{{ number_format((float) $refund_amount, 0, ',', '.') }} đ ({{ $payment_method ?? 'Thanh toán' }})</td>
                        </tr>
                    @endif
                </table>

                @if($type === 'created')
                    <p style="margin: 0;">Chúng tôi sẽ tiếp tục xử lý đơn và gửi cập nhật khi có thay đổi.</p>
                @elseif($type === 'failed')
                    <p style="margin: 0;">Nếu bạn cần hỗ trợ, vui lòng liên hệ chúng tôi để được trợ giúp.</p>
                @elseif($type === 'session_cancelled')
                    <p style="margin: 0;">Các buổi làm còn lại trong đơn hàng của bạn vẫn sẽ được thực hiện theo lịch. Cảm ơn bạn đã sử dụng dịch vụ.</p>
                @else
                    <p style="margin: 0;">Nếu đây là nhầm lẫn, vui lòng đặt lại đơn hoặc liên hệ hỗ trợ.</p>
                @endif
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
