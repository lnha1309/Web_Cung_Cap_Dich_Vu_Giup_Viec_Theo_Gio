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
        $vnp_ReturnUrl  = Config::get('vnpay.return_url');

        $vnp_TxnRef    = $params['txn_ref'];
        $vnp_OrderInfo = $params['order_info'] ?? 'Thanh toan don dat';
        $vnp_Amount    = (int) round($params['amount']) * 100;
        $vnp_IpAddr    = request()->ip();

        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $vnp_TmnCode,
            'vnp_Amount'     => $vnp_Amount,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_CurrCode'   => 'VND',
            'vnp_IpAddr'     => $vnp_IpAddr,
            'vnp_Locale'     => 'vn',
            'vnp_OrderInfo'  => $vnp_OrderInfo,
            'vnp_OrderType'  => 'billpayment',
            'vnp_ReturnUrl'  => $vnp_ReturnUrl,
            'vnp_TxnRef'     => $vnp_TxnRef,
        ];

        if (!empty($params['bank_code'])) {
            $inputData['vnp_BankCode'] = $params['bank_code'];
        }

        ksort($inputData);

        $query    = [];
        $hashData = [];
        foreach ($inputData as $key => $value) {
            $query[]    = urlencode($key) . '=' . urlencode((string) $value);
            $hashData[] = urlencode($key) . '=' . urlencode((string) $value);
        }

        $queryString = implode('&', $query);
        $hashString  = implode('&', $hashData);

        $vnpSecureHash = hash_hmac('sha512', $hashString, $vnp_HashSecret);

        return $vnp_Url . '?' . $queryString . '&vnp_SecureHash=' . $vnpSecureHash;
    }
}

