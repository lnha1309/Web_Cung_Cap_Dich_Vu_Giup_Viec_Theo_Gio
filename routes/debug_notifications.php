<?php

// Simple debug script to check notifications
// Place this in routes/web.php temporarily or run via tinker

Route::get('/debug-notifications', function () {
    if (!Auth::check()) {
        return 'Please login first';
    }
    
    $customer = Auth::user()->khachHang;
    if (!$customer) {
        return 'No customer found';
    }
    
    $notifications = \App\Models\ThongBao::where('ID_KH', $customer->ID_KH)
        ->orderBy('ThoiGian', 'desc')
        ->get();
    
    return [
        'customer_id' => $customer->ID_KH,
        'customer_name' => $customer->Ten_KH,
        'total_notifications' => $notifications->count(),
        'notifications' => $notifications->map(function($n) {
            return [
                'id' => $n->ID_TB,
                'title' => $n->TieuDe,
                'content' => $n->NoiDung,
                'type' => $n->LoaiThongBao,
                'read' => $n->DaDoc,
                'time' => $n->ThoiGian,
            ];
        })
    ];
})->middleware('auth');
