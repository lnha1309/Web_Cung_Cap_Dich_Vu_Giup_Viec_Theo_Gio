<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class VNPayService
{
    public function createPaymentUrl(array $params): string
    {
        $vnp_TmnCode    = Config::get('vnpay.tmn_code');
        $vnp_HashSecret = Config::get('vnpay.hash_secret');
        $vnp_Url        = Config::get('vnpay.url');
        $vnp_ReturnUrl  = $params['return_url'] ?? Config::get('vnpay.return_url');
        $vnp_Version    = Config::get('vnpay.version', '2.1.0');
        $vnp_Command    = Config::get('vnpay.command', 'pay');
        $vnp_CurrCode   = Config::get('vnpay.curr_code', 'VND');
        $vnp_Locale     = Config::get('vnpay.locale', 'vn');

        $vnp_TxnRef    = $params['txn_ref'];
        $vnp_OrderInfo = $params['order_info'] ?? 'Thanh toan don dat';
        $vnp_Amount    = (int) max(1, round((float) $params['amount'])) * 100; // VNPAY yêu cầu số tiền > 0
        $vnp_IpAddr    = request()->ip();

        $inputData = [
            'vnp_Version'    => $vnp_Version,
            'vnp_TmnCode'    => $vnp_TmnCode,
            'vnp_Amount'     => $vnp_Amount,
            'vnp_Command'    => $vnp_Command,
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode'   => $vnp_CurrCode,
            'vnp_IpAddr'     => $vnp_IpAddr,
            'vnp_Locale'     => $vnp_Locale,
            'vnp_OrderInfo'  => $vnp_OrderInfo,
            'vnp_OrderType'  => 'billpayment',
            'vnp_ReturnUrl'  => $vnp_ReturnUrl,
            'vnp_TxnRef'     => $vnp_TxnRef,
        ];

        if (!empty($params['bank_code'])) {
            $inputData['vnp_BankCode'] = $params['bank_code'];
        }

        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode((string) $value);
        }

        $hashString   = implode('&', $hashData);
        $vnpSecureHash = hash_hmac('sha512', $hashString, $vnp_HashSecret);

        // Add hash params after computing hash data (per VNPAY spec)
        $inputData['vnp_SecureHash'] = $vnpSecureHash;
        $inputData['vnp_SecureHashType'] = 'SHA512';

        return $vnp_Url . '?' . http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
    }
}
