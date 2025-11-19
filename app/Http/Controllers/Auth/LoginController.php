<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'TenDN' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $account = TaiKhoan::where('TenDN', $credentials['TenDN'])->first();

        if (!$account || $credentials['password'] !== $account->MatKhau) {

            return back()
                ->withErrors(['login' => 'Tên đăng nhập hoặc mật khẩu không đúng.'])
                ->withInput($request->only('TenDN'));
        }

        if ($account->TrangThaiTK === 'banned') {
            return back()
                ->withErrors(['login' => 'Tài khoản của bạn đã bị khóa.'])
                ->withInput($request->only('TenDN'));
        }

        Auth::login($account);

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

