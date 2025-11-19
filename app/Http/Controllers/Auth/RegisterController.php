<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Models\KhachHang;
use App\Models\TaiKhoan;
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('register');
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'email' => ['required', 'email', 'unique:KhachHang,Email'],
            ],
            [],
            [
                'email' => 'Email',
            ]
        );

        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        $request->session()->put('register_otp', [
            'email' => $validated['email'],
            'code' => $otp,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
        $request->session()->forget('register_otp_verified');

        Mail::to($validated['email'])->send(new RegisterOtpMail($otp));

        return response()->json([
            'status' => 'ok',
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string'],
        ]);

        $sessionOtp = $request->session()->get('register_otp');

        if (!$sessionOtp || $sessionOtp['email'] !== $data['email']) {
            return response()->json([
                'valid' => false,
                'message' => 'OTP không tồn tại. Vui lòng gửi lại.',
            ], 422);
        }

        if (Carbon::now()->greaterThan(Carbon::parse($sessionOtp['expires_at']))) {
            return response()->json([
                'valid' => false,
                'message' => 'OTP đã hết hạn. Vui lòng gửi lại.',
            ], 422);
        }

        if ($sessionOtp['code'] !== $data['otp']) {
            return response()->json([
                'valid' => false,
                'message' => 'Mã OTP không đúng.',
            ], 422);
        }

        $request->session()->put('register_otp_verified', true);

        return response()->json([
            'valid' => true,
        ]);
    }

    public function checkUsername(Request $request): JsonResponse
    {
        $data = $request->validate([
            'TenDN' => ['required', 'string', 'min:4', 'max:50'],
        ]);

        $exists = TaiKhoan::where('TenDN', $data['TenDN'])->exists();

        return response()->json([
            'available' => !$exists,
        ]);
    }

    public function checkPhone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'SDT' => ['required', 'string', 'max:15'],
        ]);

        $exists = KhachHang::where('SDT', $data['SDT'])->exists();

        return response()->json([
            'available' => !$exists,
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate(
            [
                'TenDN' => ['required', 'string', 'min:4', 'max:50', 'unique:TaiKhoan,TenDN'],
                'Ten_KH' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:KhachHang,Email'],
                'SDT' => ['required', 'string', 'max:15', 'unique:KhachHang,SDT'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ],
            [],
            [
                'TenDN' => 'Tên đăng nhập',
                'Ten_KH' => 'Họ và tên',
                'SDT' => 'Số điện thoại',
            ]
        );

        $sessionOtp = $request->session()->get('register_otp');
        $verified = $request->session()->get('register_otp_verified', false);

        if (
            !$sessionOtp ||
            !$verified ||
            $sessionOtp['email'] !== $validated['email'] ||
            Carbon::now()->greaterThan(Carbon::parse($sessionOtp['expires_at']))
        ) {
            return back()
                ->withErrors(['otp' => 'Email chưa được xác thực OTP hoặc OTP đã hết hạn.'])
                ->withInput();
        }

        $idTk = IdGenerator::next('TaiKhoan', 'ID_TK', 'TK_');
        $idKh = IdGenerator::next('KhachHang', 'ID_KH', 'KH_');

        $account = TaiKhoan::create([
            'ID_TK' => $idTk,
            'TenDN' => $validated['TenDN'],
            'MatKhau' => $validated['password'], // hiện tại login đang so sánh plain text
            'ID_LoaiTK' => 'customer',
            'TrangThaiTK' => 'active',
        ]);

        KhachHang::create([
            'ID_KH' => $idKh,
            'Ten_KH' => $validated['Ten_KH'],
            'Email' => $validated['email'],
            'SDT' => $validated['SDT'],
            'ID_TK' => $idTk,
        ]);

        $request->session()->forget(['register_otp', 'register_otp_verified']);

        Auth::login($account);

        return redirect('/')->with('status', 'Đăng ký thành công!');
    }
}
