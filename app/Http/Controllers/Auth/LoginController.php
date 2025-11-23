<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\TaiKhoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $suggestReset = $request->session()->get('login_reset_suggest');
        $request->session()->forget('login_reset_suggest');

        return view('login', [
            'suggestReset' => $suggestReset,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'TenDN'   => ['required', 'string'],
            'password'=> ['required', 'string'],
        ]);

        $username = $credentials['TenDN'];
        $attemptKey = 'login_attempts.' . $username;
        $attempts = $request->session()->get($attemptKey, 0);

        $account = TaiKhoan::where('TenDN', $username)->first();

        $passwordMatches = false;

        if ($account) {
            try {
                $passwordMatches = Hash::check($credentials['password'], $account->MatKhau);
            } catch (\RuntimeException $e) {
                $passwordMatches = false; // non-bcrypt legacy value, handle below
            }
        }

        if (!$passwordMatches && $account && $credentials['password'] === $account->MatKhau) {
            // Legacy plaintext password: upgrade to hash and allow login
            $account->MatKhau = $credentials['password'];
            $account->save();
            $passwordMatches = true;
        }

        if (!$passwordMatches) {
            $attempts++;
            $request->session()->put($attemptKey, $attempts);

            if ($attempts >= 3) {
                $request->session()->put('login_reset_suggest', $username);
            }

            return back()
                ->withErrors(['login' => 'Ten dang nhap hoac mat khau khong dung.'])
                ->withInput($request->only('TenDN'));
        }

        if ($account->TrangThaiTK === 'banned') {
            return back()
                ->withErrors(['login' => 'Tai khoan cua ban da bi khoa.'])
                ->withInput($request->only('TenDN'));
        }

        if (Hash::needsRehash($account->MatKhau)) {
            $account->MatKhau = $credentials['password'];
            $account->save();
        }

        Auth::login($account);

        $request->session()->forget($attemptKey);
        $request->session()->forget('login_reset_suggest');

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function sendResetOtp(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'email'    => ['required', 'email'],
        ]);

        $username = $data['username'];
        $email = $data['email'];

        $account = $this->findAccountByUsernameAndEmail($username, $email);

        if (!$account) {
            return response()->json([
                'message' => 'Khong tim thay tai khoan voi ten dang nhap va email nay.',
            ], 404);
        }

        $otp = (string) random_int(100000, 999999);

        $request->session()->put('password_reset_otp', [
            'username'   => $username,
            'email'      => $email,
            'code'       => $otp,
            'expires_at' => Carbon::now()->addMinutes(10)->timestamp,
        ]);

        Mail::to($email)->send(new ResetPasswordOtpMail($otp));

        return response()->json([
            'message' => 'Da gui ma OTP toi email cua ban. Ma co hieu luc trong 10 phut.',
        ]);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'email'    => ['required', 'email'],
            'otp'      => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $sessionOtp = $request->session()->get('password_reset_otp');
        if (!$sessionOtp) {
            return response()->json([
                'message' => 'Chua gui OTP hoac OTP da het han, vui long gui lai.',
            ], 422);
        }

        if (
            $sessionOtp['email'] !== $data['email'] ||
            $sessionOtp['username'] !== $data['username'] ||
            $sessionOtp['code'] !== $data['otp']
        ) {
            return response()->json([
                'message' => 'OTP khong dung hoac thong tin khong khop.',
            ], 422);
        }

        if (Carbon::now()->timestamp > ($sessionOtp['expires_at'] ?? 0)) {
            $request->session()->forget('password_reset_otp');
            return response()->json([
                'message' => 'OTP da het han, vui long gui lai.',
            ], 422);
        }

        $account = $this->findAccountByUsernameAndEmail($data['username'], $data['email']);
        if (!$account) {
            return response()->json([
                'message' => 'Khong tim thay tai khoan voi ten dang nhap va email nay.',
            ], 404);
        }

        $account->MatKhau = $data['password'];
        $account->save();

        $request->session()->forget('password_reset_otp');

        return response()->json([
            'message' => 'Dat lai mat khau thanh cong. Vui long dang nhap voi mat khau moi.',
        ]);
    }

    private function findAccountByUsernameAndEmail(string $username, string $email): ?TaiKhoan
    {
        return TaiKhoan::where('TenDN', $username)
            ->where(function ($query) use ($email) {
                $query->whereHas('khachHang', function ($sub) use ($email) {
                    $sub->where('Email', $email);
                })->orWhereHas('nhanVien', function ($sub) use ($email) {
                    $sub->where('Email', $email);
                });
            })
            ->first();
    }
}
