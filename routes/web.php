<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('home');
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
});

Route::get('/workerintroduction', function () {
    return view('workerintroduction');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
    Route::post('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
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
