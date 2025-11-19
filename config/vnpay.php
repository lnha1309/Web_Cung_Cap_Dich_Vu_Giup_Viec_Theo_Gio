<?php

return [
    'tmn_code'    => env('VNP_TMN_CODE', '773WKFCP'),
    'hash_secret' => env('VNP_HASH_SECRET', '0MR10D647EU8UNJ9KTA1TBZM5O2DQPRT'),
    'url'         => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'return_url'  => env('VNP_RETURN_URL', env('APP_URL') . '/payment/vnpay-return'),
];

