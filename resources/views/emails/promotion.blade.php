<!DOCTYPE html>
<html>
<head>
    <title>Thông báo khuyến mãi từ bTaskee</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { background-color: #FF7B29; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .promo-code { font-size: 24px; font-weight: bold; color: #FF7B29; text-align: center; margin: 20px 0; padding: 10px; border: 2px dashed #FF7B29; display: inline-block; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ưu đãi đặc biệt dành cho bạn!</h1>
        </div>
        <div class="content">
            <p>Xin chào quý khách,</p>
            
            <p>{{ $note }}</p>
            
            <div style="text-align: center;">
                <div class="promo-code">{{ $promotion->ID_KM }}</div>
            </div>
            
            <p><strong>Giảm giá:</strong> {{ $promotion->PhanTramGiam }}% (Tối đa {{ number_format($promotion->GiamToiDa) }} đ)</p>
            
            @if($promotion->NgayHetHan)
            <p><strong>Hạn sử dụng:</strong> {{ \Carbon\Carbon::parse($promotion->NgayHetHan)->format('d/m/Y') }}</p>
            @endif
            
            <p>Hãy nhanh tay sử dụng mã khuyến mãi này cho đơn hàng tiếp theo của bạn!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} bTaskee. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
