<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Mail\OtpMail;
use App\Mail\ResetPasswordOtpMail;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class ApiAuthController extends Controller
{
    /**
     * Register a new user
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:TaiKhoan,TenDN'],
            'password' => ['required', 'string', 'min:6'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:KhachHang,Email'],
            'phone' => ['required', 'string', 'max:15', 'unique:KhachHang,SDT'],
            'otp' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify OTP
        $cacheKey = 'register_otp_' . $request->phone;
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'success' => false,
                'error' => 'OTP không hợp lệ hoặc đã hết hạn.',
            ], 422);
        }

        try {
            // Create TaiKhoan
            $idTk = IdGenerator::next('TaiKhoan', 'ID_TK', 'TK_');
            $taiKhoan = TaiKhoan::create([
                'ID_TK' => $idTk,
                'TenDN' => $request->username,
                'MatKhau' => Hash::make($request->password),
                'ID_LoaiTK' => 'customer', // adjust to your account type id
                'TrangThaiTK' => 'inactive',
            ]);

            // Create KhachHang
            $idKh = IdGenerator::next('KhachHang', 'ID_KH', 'KH_');
            $khachHang = KhachHang::create([
                'ID_KH' => $idKh,
                'Ten_KH' => $request->full_name,
                'Email' => $request->email,
                'SDT' => $request->phone,
                'ID_TK' => $idTk,
            ]);

            // Clear OTP from cache
            Cache::forget($cacheKey);

            $taiKhoan->name = $khachHang->Ten_KH ?? $taiKhoan->TenDN;
            $taiKhoan->email = $khachHang->Email ?? null;
            $taiKhoan->save();

            $token = $taiKhoan->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công.',
                'data' => [
                    'user' => [
                        'id' => $taiKhoan->ID_TK,
                        'username' => $taiKhoan->TenDN,
                        'full_name' => $khachHang->Ten_KH,
                        'email' => $khachHang->Email,
                        'phone' => $khachHang->SDT,
                        'account_type' => $taiKhoan->ID_LoaiTK ?? null,
                    ],
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:customer,staff'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $taiKhoan = TaiKhoan::where('TenDN', $request->username)->first();

        // Role-based gate
        $role = $request->input('role');
        $accountType = $taiKhoan->ID_LoaiTK ?? null;
        if ($taiKhoan) {
            if ($accountType === 'admin') {
                return response()->json([
                    'success' => false,
                    'error' => 'Tài khoản không được phép đăng nhập từ ứng dụng.',
                ], 403);
            }

            if ($role === 'customer' && $accountType !== 'customer') {
                return response()->json([
                    'success' => false,
                    'error' => 'Chỉ đăng nhập bằng tài khoản khách hàng.',
                ], 403);
            }

            if ($role === 'staff' && $accountType !== 'staff') {
                return response()->json([
                    'success' => false,
                    'error' => 'Chỉ đăng nhập bằng tài khoản nhân viên.',
                ], 403);
            }
        }

        $validPassword = false;
        if ($taiKhoan) {
            try {
                $validPassword = Hash::check($request->password, $taiKhoan->MatKhau);
            } catch (\RuntimeException $e) {
                // Fallback for legacy plain-text passwords
                $validPassword = $request->password === $taiKhoan->MatKhau;
            }
        }

        if (!$taiKhoan || !$validPassword) {
            return response()->json([
                'success' => false,
                'error' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
            ], 401);
        }

        // Check if account is active
        if ($taiKhoan->TrangThaiTK !== 'active') {
            return response()->json([
                'success' => false,
                'error' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ tổng đài.',
            ], 403);
        }

        // Get user data
        $userData = [
            'id' => $taiKhoan->ID_TK,
            'username' => $taiKhoan->TenDN,
            'account_type' => $taiKhoan->ID_LoaiTK,
        ];

        if ($role === 'customer') {
            $khachHang = KhachHang::where('ID_TK', $taiKhoan->ID_TK)->first();
            if ($khachHang) {
                $userData['full_name'] = $khachHang->Ten_KH;
                $userData['email'] = $khachHang->Email;
                $userData['phone'] = $khachHang->SDT;
            }
        } elseif ($role === 'staff') {
            // Assuming staff data is in a different model, adjust accordingly
            // For now, placeholder
            $userData['full_name'] = $taiKhoan->name ?? $taiKhoan->TenDN;
            $userData['email'] = $taiKhoan->email ?? null;
            // Add staff specific data if needed
        }

        $token = $taiKhoan->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'data' => [
                'user' => $userData,
                'token' => $token,
            ]
        ], 200);
    }

    /**
     * Get authenticated user profile
     * GET /api/auth/profile
     */
    public function profile(Request $request)
    {
        $taiKhoan = $request->user();

        if (!$taiKhoan) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy người dùng.',
            ], 401);
        }

        $accountType = $taiKhoan->ID_LoaiTK;

        $userData = [
            'id' => $taiKhoan->ID_TK,
            'username' => $taiKhoan->TenDN,
            'account_type' => $accountType,
        ];

        if ($accountType === 'customer') {
            $khachHang = KhachHang::where('ID_TK', $taiKhoan->ID_TK)->first();
            if ($khachHang) {
                $userData['ID_KH'] = $khachHang->ID_KH;
                $userData['full_name'] = $khachHang->Ten_KH;
                $userData['email'] = $khachHang->Email;
                $userData['phone'] = $khachHang->SDT;
                $userData['HinhAnh'] = $khachHang->HinhAnh ?? null;
            }
        } elseif ($accountType === 'staff') {
            $nhanVien = NhanVien::where('ID_TK', $taiKhoan->ID_TK)->first();
            if ($nhanVien) {
                $userData['ID_NV'] = $nhanVien->ID_NV;
                $userData['Ten_NV'] = $nhanVien->Ten_NV;
                $userData['full_name'] = $nhanVien->Ten_NV;
                $userData['email'] = $nhanVien->Email;
                $userData['SDT'] = $nhanVien->SDT;
                $userData['phone'] = $nhanVien->SDT;
                $userData['NgaySinh'] = $nhanVien->NgaySinh;
                $userData['GioiTinh'] = $nhanVien->GioiTinh;
                $userData['ID_Quan'] = $nhanVien->ID_Quan;
                $userData['KhuVucLamViec'] = $nhanVien->KhuVucLamViec;
                
                // Build full URL for avatar image
                $avatarPath = $nhanVien->HinhAnh;
                if ($avatarPath) {
                    // Check if it's already a full URL
                    if (filter_var($avatarPath, FILTER_VALIDATE_URL)) {
                        $userData['HinhAnh'] = $avatarPath;
                    } else {
                        $userData['HinhAnh'] = url('storage/' . $avatarPath);
                    }
                } else {
                    $userData['HinhAnh'] = null;
                }
                
                $userData['SoDu'] = $nhanVien->SoDu;
                $userData['TrangThai'] = $nhanVien->TrangThai;

                // Calculate average rating
                $avgRating = $nhanVien->danhGias()->avg('Diem') ?? 0;
                $userData['avg_rating'] = round($avgRating, 1);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData,
            ]
        ], 200);
    }

    /**
     * Save push notification token (OneSignal player_id)
     * POST /api/auth/push-token
     */
    public function savePushToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'player_id' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $taiKhoan = $request->user();

        if (!$taiKhoan) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy người dùng.',
            ], 401);
        }

        try {
            // Store push token in TaiKhoan
            $taiKhoan->onesignal_player_id = $request->player_id;
            $taiKhoan->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu push token.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Lỗi khi lưu push token: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user - revoke current token
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Không tìm thấy người dùng.',
            ], 401);
        }

        try {
            // Revoke the current access token
            $user->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Lỗi khi đăng xuất: ' . $e->getMessage(),
            ], 500);
        }
    }
}
