<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\DonDat;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminPackageController;
use App\Http\Controllers\AdminSurchargeController;
use App\Http\Controllers\AdminPromotionController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\ContactController;

Route::get('/', function () {
    $showNewCustomerVoucher = true;

    if (Auth::check() && Auth::user()->khachHang) {
        $customerId = Auth::user()->khachHang->ID_KH;

        $hasUsedVoucher = DonDat::where('ID_KH', $customerId)
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('ChiTietKhuyenMai')
                    ->whereColumn('ChiTietKhuyenMai.ID_DD', 'DonDat.ID_DD')
                    ->where('ChiTietKhuyenMai.ID_KM', 'KHACHHANGMOI');
            })
            ->exists();

        $showNewCustomerVoucher = !$hasUsedVoucher;
    }

    return view('home', [
        'showNewCustomerVoucher' => $showNewCustomerVoucher,
    ]);
});

Route::get('/appintroduction', function () {
    return view('appintroduction');
});

Route::get('/introduction', function () {
    return view('introduction');
});

Route::get('/post', function () {
    return view('post');
});

Route::get('/post-detail-1', function () {
    return view('post-detail-1');
});

Route::get('/post-detail-2', function () {
    return view('post-detail-2');
});

Route::get('/post-detail-3', function () {
    return view('post-detail-3');
});

Route::get('/contact', function () {
    return view('contact');
})->name('contact.show');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

Route::get('/workerintroduction', function () {
    return view('workerintroduction');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/password/send-otp', [LoginController::class, 'sendResetOtp'])->name('password.sendOtp');
Route::post('/password/reset-with-otp', [LoginController::class, 'resetPasswordWithOtp'])->name('password.resetWithOtp');

Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register/send-otp', [RegisterController::class, 'sendOtp'])->name('register.sendOtp');
Route::post('/register/verify-otp', [RegisterController::class, 'verifyOtp'])->name('register.verifyOtp');
Route::post('/register/check-username', [RegisterController::class, 'checkUsername'])->name('register.checkUsername');
Route::post('/register/check-phone', [RegisterController::class, 'checkPhone'])->name('register.checkPhone');
Route::post('/register', [RegisterController::class, 'register'])->name('register.post');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/select-address', [BookingController::class, 'selectAddress'])->name('booking.selectAddress');
    Route::get('/booking', [BookingController::class, 'show'])->name('booking.show');

    Route::post('/booking/quote-hour', [BookingController::class, 'quoteHour'])->name('booking.quoteHour');
    Route::post('/booking/find-staff', [BookingController::class, 'findStaff'])->name('booking.findStaff');
    Route::post('/booking/apply-voucher', [BookingController::class, 'applyVoucher'])->name('booking.applyVoucher');
    Route::get('/booking/surcharges', [BookingController::class, 'getSurcharges'])->name('booking.surcharges');
    Route::post('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('services', AdminServiceController::class)->except(['create', 'edit', 'show']);
        Route::resource('packages', AdminPackageController::class)->except(['create', 'edit', 'show']);
        Route::resource('surcharges', AdminSurchargeController::class)->except(['create', 'edit', 'show']);
        Route::resource('promotions', AdminPromotionController::class)->except(['create', 'edit', 'show']);
        Route::patch('promotions/{promotion}/toggle', [AdminPromotionController::class, 'toggle'])->name('promotions.toggle');
    });

    Route::get('/my-bookings', [BookingController::class, 'history'])->name('bookings.history');
    Route::get('/my-bookings/{id}', [BookingController::class, 'detail'])->name('bookings.detail');
    Route::post('/my-bookings/{id}/cancel', [BookingController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::post('/my-bookings/{id}/rating', [BookingController::class, 'submitRating'])->name('bookings.rating');

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');

    // Notification API for web session (avoid Sanctum token requirement)
    Route::prefix('web-api/notifications')->group(function () {
        Route::get('/', [ApiNotificationController::class, 'index']);
        Route::get('/unread-count', [ApiNotificationController::class, 'unreadCount']);
        Route::post('/{id}/mark-read', [ApiNotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [ApiNotificationController::class, 'markAllAsRead']);
    });

});

Route::get('/payment/vnpay-return', [BookingController::class, 'vnpayReturn'])->name('vnpay.return');

Route::get('/apply', function () {
    return view('apply');
});

Route::get('/giupviectheogio', function () {
    return view('giupviectheogio');
});

Route::get('/giupviectheothang', function () {
    return view('giupviectheothang');
});

// Debug routes
if (file_exists(__DIR__ . '/web_test_debug.php')) {
    require __DIR__ . '/web_test_debug.php';
}

// Temporary debug route for notifications
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
