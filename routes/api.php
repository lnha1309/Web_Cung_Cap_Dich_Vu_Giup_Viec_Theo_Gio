<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiServiceController;
use App\Http\Controllers\Api\ApiAddressController;
use App\Http\Controllers\Api\ApiBookingController;
use App\Http\Controllers\Api\ApiVoucherController;
use App\Http\Controllers\Api\ApiStaffScheduleController;
use App\Http\Controllers\Api\ApiStaffBookingController;
use App\Http\Controllers\Api\ApiNotificationController;
use App\Http\Controllers\Api\ApiStaffWalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes - No authentication required
Route::prefix('auth')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/send-otp', [ApiAuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [ApiAuthController::class, 'verifyOtp']);
    Route::post('/check-username', [ApiAuthController::class, 'checkUsername']);
    Route::post('/check-phone', [ApiAuthController::class, 'checkPhone']);
    Route::post('/check-email', [ApiAuthController::class, 'checkEmail']);
    Route::post('/password/send-otp', [ApiAuthController::class, 'sendResetOtp']);
    Route::post('/password/reset', [ApiAuthController::class, 'resetPasswordWithOtp']);
});

// Public service routes
Route::prefix('services')->group(function () {
    Route::get('/', [ApiServiceController::class, 'index']);
    Route::get('/by-duration/{hours}', [ApiServiceController::class, 'getByDuration']);
    Route::get('/{id}', [ApiServiceController::class, 'show']);
    Route::post('/quote', [ApiServiceController::class, 'calculateQuote']);
});

// Protected routes - Require authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::post('/change-password', [ApiAuthController::class, 'changePassword']);
        Route::get('/profile', [ApiAuthController::class, 'profile']);
        Route::put('/profile', [ApiAuthController::class, 'updateProfile']);
        Route::post('/push-token', [ApiAuthController::class, 'savePushToken']);
        Route::post('/profile/avatar', [ApiAuthController::class, 'uploadAvatar']);
    });

    // Address routes
    Route::prefix('addresses')->group(function () {
        Route::get('/', [ApiAddressController::class, 'index']);
        Route::post('/', [ApiAddressController::class, 'store']);
        Route::get('/{id}', [ApiAddressController::class, 'show']);
        Route::put('/{id}', [ApiAddressController::class, 'update']);
        Route::delete('/{id}', [ApiAddressController::class, 'destroy']);
    });

    // Booking routes
    Route::prefix('bookings')->group(function () {
        Route::get('/', [ApiBookingController::class, 'index']);
        Route::post('/', [ApiBookingController::class, 'store']);
        Route::get('/{id}', [ApiBookingController::class, 'show']);
        Route::post('/find-staff', [ApiBookingController::class, 'findStaff']);
        Route::post('/quote', [ApiBookingController::class, 'calculateQuote']);
        Route::put('/{id}/cancel', [ApiBookingController::class, 'cancel']);
        Route::post('/{id}/rate', [ApiBookingController::class, 'rate']);
    });

    // Voucher routes
    Route::prefix('vouchers')->group(function () {
        Route::get('/', [ApiVoucherController::class, 'index']);
        Route::post('/apply', [ApiVoucherController::class, 'apply']);
        Route::get('/history', [ApiVoucherController::class, 'history']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [ApiNotificationController::class, 'index']);
        Route::get('/unread-count', [ApiNotificationController::class, 'unreadCount']);
        Route::post('/{id}/mark-read', [ApiNotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [ApiNotificationController::class, 'markAllAsRead']);
    });

    // Staff schedules
    Route::prefix('staff')->group(function () {
        Route::get('/schedules', [ApiStaffScheduleController::class, 'index']);
        Route::put('/schedules', [ApiStaffScheduleController::class, 'update']);
        Route::post('/schedules', [ApiStaffScheduleController::class, 'store']);

        Route::get('/wallet', [ApiStaffWalletController::class, 'summary']);
        Route::get('/wallet/history', [ApiStaffWalletController::class, 'history']);
        Route::get('/wallet/history/{id}', [ApiStaffWalletController::class, 'show']);
        Route::post('/wallet/topup', [ApiStaffWalletController::class, 'topup']);

        Route::get('/bookings/available', [ApiStaffBookingController::class, 'available']);
        Route::get('/bookings', [ApiStaffBookingController::class, 'index']);
        Route::get('/bookings/{id}', [ApiStaffBookingController::class, 'show']);
        Route::post('/bookings/{id}/confirm', [ApiStaffBookingController::class, 'confirm']);
        Route::post('/bookings/{id}/reject', [ApiStaffBookingController::class, 'reject']);
        Route::post('/bookings/{id}/claim', [ApiStaffBookingController::class, 'claim']);
        Route::post('/bookings/{id}/complete', [ApiStaffBookingController::class, 'complete']);
        Route::get('/month-sessions/available', [ApiStaffBookingController::class, 'availableMonthSessions']);
        Route::post('/month-sessions/{id}/claim', [ApiStaffBookingController::class, 'claimMonthSession']);
        Route::post('/month-sessions/{id}/reject', [ApiStaffBookingController::class, 'rejectMonthSession']);
        Route::get('/earnings', [ApiStaffBookingController::class, 'earnings']);
        Route::get('/weekly-report', [ApiStaffBookingController::class, 'weeklyReport']);
    });
});
