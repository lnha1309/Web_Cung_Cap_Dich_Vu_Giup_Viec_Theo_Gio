<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaiKhoan;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Models\LichLamViec;
use App\Models\DanhGiaNhanVien;
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\OtpMail;

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
            'email' => ['required', 'email', 'max:255'],
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
                'error' => 'OTP khong hop le hoac da het han.',
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
                'message' => 'Dang ky thanh cong.',
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
                'error' => 'Loi he thong: ' . $e->getMessage(),
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
                    'error' => 'Tai khoan khong duoc phep dang nhap tu ung dung.',
                ], 403);
            }

            if ($role === 'customer' && $accountType !== 'customer') {
                return response()->json([
                    'success' => false,
                    'error' => 'Chi dang nhap bang tai khoan khach hang.',
                ], 403);
            }

            if ($role === 'staff' && $accountType !== 'staff') {
                return response()->json([
                    'success' => false,
                    'error' => 'Chi dang nhap bang tai khoan nhan vien.',
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
                'error' => 'Ten dang nhap hoac mat khau khong dung.',
            ], 401);
        }

        if (in_array($taiKhoan->TrangThaiTK, ['banned', 'locked'], true)) {
            return response()->json([
                'success' => false,
                'error' => 'Tai khoan cua ban da bi khoa. Vui long lien he tong dai.',
            ], 403);
        }

        if ($taiKhoan->TrangThaiTK === 'inactive') {
            return response()->json([
                'success' => false,
                'error' => 'Tai khoan cua ban dang cho kich hoat. Vui long lien he tong dai.',
            ], 403);
        }

        // Lock if vi pham 2 tuan lien tiep khong dang ky lich (khong can cron)
        if ($taiKhoan->ID_LoaiTK === 'staff' && $this->shouldLockForMissingSchedules($taiKhoan)) {
            $taiKhoan->TrangThaiTK = 'locked';
            $taiKhoan->save();

            return response()->json([
                'success' => false,
                'error' => 'Tai khoan cua ban da bi khoa do khong dang ky lich 2 tuan lien tiep. Vui long lien he tong dai.',
            ], 403);
        }

        $token = $taiKhoan->createToken('mobile-app')->plainTextToken;
        $khachHang = $taiKhoan->khachHang;
        $nhanVien = $taiKhoan->nhanVien;

        $userData = [
            'id' => $taiKhoan->ID_TK,
            'username' => $taiKhoan->TenDN,
            'account_type' => $taiKhoan->ID_LoaiTK ?? null,
        ];

        if ($taiKhoan->ID_LoaiTK === 'staff' && $nhanVien) {
            $userData['full_name'] = $nhanVien->Ten_NV ?? '';
            $userData['email'] = $nhanVien->Email ?? '';
            $userData['phone'] = $nhanVien->SDT ?? '';
            $ratingStats = $this->staffRatingStats($nhanVien);
            $userData['avg_rating'] = $ratingStats['avg_rating'];
            $userData['rating_count'] = $ratingStats['rating_count'];
            $userData['staff'] = [
                'ID_NV' => $nhanVien->ID_NV,
                'Ten_NV' => $nhanVien->Ten_NV,
                'SDT' => $nhanVien->SDT,
                'Email' => $nhanVien->Email,
                'GioiTinh' => $nhanVien->GioiTinh,
                'NgaySinh' => $nhanVien->NgaySinh,
                'KhuVucLamViec' => $nhanVien->KhuVucLamViec,
                'HinhAnh' => $this->avatarUrl($nhanVien->HinhAnh),
                'SoDu' => $nhanVien->SoDu,
                'ID_Quan' => $nhanVien->ID_Quan,
            ];
        } else {
            $userData['full_name'] = $khachHang?->Ten_KH ?? '';
            $userData['email'] = $khachHang?->Email ?? '';
            $userData['phone'] = $khachHang?->SDT ?? '';
        }

        return response()->json([
            'success' => true,
            'message' => 'Dang nhap thanh cong.',
            'data' => [
                'user' => $userData,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dang xuat thanh cong.'
        ]);
    }

    /**
     * Change password for authenticated user
     * POST /api/auth/change-password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        /** @var \App\Models\TaiKhoan|null $taiKhoan */
        $taiKhoan = $request->user();
        if (!$taiKhoan) {
            return response()->json([
                'success' => false,
                'error' => 'Khong xac thuc duoc nguoi dung.',
            ], 401);
        }

        $currentOk = false;
        try {
            $currentOk = Hash::check($request->current_password, $taiKhoan->MatKhau);
        } catch (\RuntimeException $e) {
            $currentOk = $request->current_password === $taiKhoan->MatKhau;
        }

        if (!$currentOk) {
            return response()->json([
                'success' => false,
                'error' => 'Mat khau hien tai khong dung.',
            ], 400);
        }

        $taiKhoan->MatKhau = Hash::make($request->new_password);
        $taiKhoan->save();

        // (Optional) revoke all other tokens and keep current token alive
        $request->user()->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Doi mat khau thanh cong.',
        ]);
    }

    /**
     * Get user profile
     * GET /api/auth/profile
     */
    public function profile(Request $request)
    {
        /** @var \App\Models\TaiKhoan|null $taiKhoan */
        $taiKhoan = $request->user();
        $khachHang = $taiKhoan?->khachHang;
        $nhanVien = $taiKhoan?->nhanVien;

        $userData = [
            'id' => $taiKhoan->ID_TK ?? null,
            'username' => $taiKhoan->TenDN ?? '',
            'account_type' => $taiKhoan->ID_LoaiTK ?? null,
        ];

        if ($taiKhoan && $taiKhoan->ID_LoaiTK === 'staff' && $nhanVien) {
            $userData['full_name'] = $nhanVien->Ten_NV ?? '';
            $userData['email'] = $nhanVien->Email ?? '';
            $userData['phone'] = $nhanVien->SDT ?? '';
            $ratingStats = $this->staffRatingStats($nhanVien);
            $userData['avg_rating'] = $ratingStats['avg_rating'];
            $userData['rating_count'] = $ratingStats['rating_count'];
            $userData['staff'] = [
                'ID_NV' => $nhanVien->ID_NV,
                'Ten_NV' => $nhanVien->Ten_NV,
                'SDT' => $nhanVien->SDT,
                'Email' => $nhanVien->Email,
                'GioiTinh' => $nhanVien->GioiTinh,
                'NgaySinh' => $nhanVien->NgaySinh,
                'KhuVucLamViec' => $nhanVien->KhuVucLamViec,
                'HinhAnh' => $this->avatarUrl($nhanVien->HinhAnh),
                'SoDu' => $nhanVien->SoDu,
                'ID_Quan' => $nhanVien->ID_Quan,
            ];
        } else {
            $userData['full_name'] = $khachHang?->Ten_KH ?? '';
            $userData['email'] = $khachHang?->Email ?? '';
            $userData['phone'] = $khachHang?->SDT ?? '';
        }

        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }

    /**
     * Update user profile
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request)
    {
        $rules = [
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ];

        /** @var \App\Models\TaiKhoan|null $taiKhoan */
        $taiKhoan = $request->user();

        if ($taiKhoan && $taiKhoan->ID_LoaiTK === 'staff') {
            $rules = array_merge($rules, [
                'phone' => ['nullable', 'string', 'max:15'],
                'gender' => ['nullable', 'in:male,female,nam,nu,nữ'],
                'birth_date' => ['nullable', 'date'],
                'khu_vuc_lam_viec' => ['nullable', 'string', 'max:255'],
                'id_quan' => ['nullable', 'string', 'max:50'],
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($taiKhoan && $taiKhoan->ID_LoaiTK === 'staff') {
            $nhanVien = $taiKhoan->nhanVien;
            if (!$nhanVien) {
                return response()->json([
                    'success' => false,
                    'error' => 'Khong tim thay thong tin nhan vien.'
                ], 404);
            }

            if ($request->has('full_name')) {
                $nhanVien->Ten_NV = $request->full_name;
            }
            if ($request->has('email')) {
                $nhanVien->Email = $request->email;
            }
            if ($request->has('phone')) {
                $nhanVien->SDT = $request->phone;
            }
            if ($request->has('gender')) {
                $nhanVien->GioiTinh = $request->gender;
            }
            if ($request->has('birth_date')) {
                $nhanVien->NgaySinh = $request->birth_date;
            }
            if ($request->has('khu_vuc_lam_viec')) {
                $nhanVien->KhuVucLamViec = $request->khu_vuc_lam_viec;
            }
            if ($request->has('id_quan')) {
                $nhanVien->ID_Quan = $request->id_quan;
            }
            $nhanVien->save();
            $ratingStats = $this->staffRatingStats($nhanVien);

            return response()->json([
                    'success' => true,
                    'message' => 'Cap nhat thong tin thanh cong.',
                    'data' => [
                    'id' => $taiKhoan->ID_TK,
                    'username' => $taiKhoan->TenDN ?? '',
                    'account_type' => $taiKhoan->ID_LoaiTK ?? null,
                    'full_name' => $nhanVien->Ten_NV ?? '',
                    'email' => $nhanVien->Email ?? '',
                    'phone' => $nhanVien->SDT ?? '',
                    'avg_rating' => $ratingStats['avg_rating'],
                    'rating_count' => $ratingStats['rating_count'],
                    'staff' => [
                        'ID_NV' => $nhanVien->ID_NV,
                        'Ten_NV' => $nhanVien->Ten_NV,
                        'SDT' => $nhanVien->SDT,
                        'Email' => $nhanVien->Email,
                        'GioiTinh' => $nhanVien->GioiTinh,
                        'NgaySinh' => $nhanVien->NgaySinh,
                        'KhuVucLamViec' => $nhanVien->KhuVucLamViec,
                        'HinhAnh' => $this->avatarUrl($nhanVien->HinhAnh),
                        'SoDu' => $nhanVien->SoDu,
                        'ID_Quan' => $nhanVien->ID_Quan,
                    ],
                ]
            ]);
        } else {
            $khachHang = $taiKhoan?->khachHang;

            if (!$khachHang) {
                return response()->json([
                    'success' => false,
                    'error' => 'Khong tim thay thong tin khach hang.'
                ], 404);
            }

            if ($request->has('full_name')) {
                $khachHang->Ten_KH = $request->full_name;
            }

            if ($request->has('email')) {
                $khachHang->Email = $request->email;
            }

            $khachHang->save();

            return response()->json([
                'success' => true,
                'message' => 'Cap nhat thong tin thanh cong.',
                'data' => [
                    'id' => $taiKhoan->ID_TK,
                    'username' => $taiKhoan->TenDN ?? '',
                    'full_name' => $khachHang->Ten_KH,
                    'email' => $khachHang->Email,
                    'phone' => $khachHang->SDT,
                ]
            ]);
        }
    }

    /**
     * Send OTP for registration
     * POST /api/auth/send-otp
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:15'],
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if phone already exists
        $existingCustomer = KhachHang::where('SDT', $request->phone)->first();
        if ($existingCustomer) {
            return response()->json([
                'success' => false,
                'error' => 'So dien thoai da duoc su dung.'
            ], 422);
        }

        // Generate OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 5 minutes
        $cacheKey = 'register_otp_' . $request->phone;
        Cache::put($cacheKey, $otp, now()->addMinutes(5));

        // Send OTP via email
        try {
            Mail::to($request->email)->send(new OtpMail($otp));

            return response()->json([
                'success' => true,
                'message' => 'OTP da duoc gui den email cua ban.',
                'debug_otp' => config('app.debug') ? $otp : null, // Only in debug mode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Khong the gui OTP. Vui long thu lai.',
                'debug_error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify OTP
     * POST /api/auth/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cacheKey = 'register_otp_' . $request->phone;
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'success' => false,
                'error' => 'OTP khong hop le hoac da het han.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP hop le.'
        ]);
    }

    /**
     * Check if username is available
     * POST /api/auth/check-username
     */
    public function checkUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = TaiKhoan::where('TenDN', $request->username)->exists();

        return response()->json([
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'Ten dang nhap da ton tai.' : 'Ten dang nhap kha dung.'
        ]);
    }

    /**
     * Check if phone is available
     * POST /api/auth/check-phone
     */
    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:15'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = KhachHang::where('SDT', $request->phone)->exists();

        return response()->json([
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'So dien thoai da duoc su dung.' : 'So dien thoai kha dung.'
        ]);
    }

    /**
     * Save OneSignal player ID for current user
     * POST /api/auth/push-token
     */
    public function savePushToken(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Khong xac thuc duoc nguoi dung.',
            ], 401);
        }

        $playerId = $request->input('player_id');
        if (!$playerId || !is_string($playerId)) {
            return response()->json([
                'success' => false,
                'error' => 'player_id khong hop le.',
            ], 422);
        }

        $user->onesignal_player_id = $playerId;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Da cap nhat push token.',
        ]);
    }

    /**
     * Upload avatar for staff
     * POST /api/auth/profile/avatar
     */
    public function uploadAvatar(Request $request)
    {
        $user = $request->user();
        $nhanVien = $user?->nhanVien;

        if (!$user || $user->ID_LoaiTK !== 'staff' || !$nhanVien) {
            return response()->json([
                'success' => false,
                'error' => 'Chi nhan vien moi duoc cap nhat anh dai dien.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'max:2048'], // 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('avatar');
        if (!$file) {
            return response()->json([
                'success' => false,
                'error' => 'Tep khong hop le.',
            ], 422);
        }

        $path = $file->store('avatars', 'public'); // returns relative path inside disk
        $nhanVien->HinhAnh = $path; // store relative, easier to change domain later
        $nhanVien->save();

        $ratingStats = $this->staffRatingStats($nhanVien);

        return response()->json([
            'success' => true,
            'message' => 'Da cap nhat anh dai dien.',
            'data' => [
                'avatar_url' => $this->avatarUrl($path),
                'id' => $user->ID_TK,
                'username' => $user->TenDN,
                'account_type' => $user->ID_LoaiTK,
                'full_name' => $nhanVien->Ten_NV ?? '',
                'email' => $nhanVien->Email ?? '',
                'phone' => $nhanVien->SDT ?? '',
                'avg_rating' => $ratingStats['avg_rating'],
                'rating_count' => $ratingStats['rating_count'],
                'staff' => [
                    'ID_NV' => $nhanVien->ID_NV,
                    'Ten_NV' => $nhanVien->Ten_NV,
                    'SDT' => $nhanVien->SDT,
                    'Email' => $nhanVien->Email,
                    'GioiTinh' => $nhanVien->GioiTinh,
                    'NgaySinh' => $nhanVien->NgaySinh,
                    'KhuVucLamViec' => $nhanVien->KhuVucLamViec,
                    'HinhAnh' => $this->avatarUrl($nhanVien->HinhAnh),
                    'SoDu' => $nhanVien->SoDu,
                    'ID_Quan' => $nhanVien->ID_Quan,
                ],
            ],
        ]);
    }

    private function staffRatingStats(?NhanVien $nhanVien): array
    {
        if (!$nhanVien) {
            return [
                'avg_rating' => 0.0,
                'rating_count' => 0,
            ];
        }

        $ratingsQuery = DanhGiaNhanVien::where('ID_NV', $nhanVien->ID_NV);
        $avg = (float) $ratingsQuery->avg('Diem');
        $count = (int) $ratingsQuery->count();

        return [
            'avg_rating' => $count > 0 ? round($avg, 2) : 0.0,
            'rating_count' => $count,
        ];
    }

    private function avatarUrl(?string $stored): ?string
    {
        if (!$stored || trim($stored) === '') {
            return null;
        }

        if (Str::startsWith($stored, ['http://', 'https://'])) {
            return $stored;
        }

        $relative = Storage::url($stored); // usually /storage/...

        // Ưu tiên host theo request hiện tại (phù hợp khi app dùng 10.0.2.2)
        $host = request()->getSchemeAndHttpHost();
        if (!$host || $host === 'http://localhost') {
          $host = config('app.url') ?: url('/');
        }
        $host = rtrim($host, '/');
        return $host . $relative;
    }

    private function shouldLockForMissingSchedules(TaiKhoan $taiKhoan): bool
    {
        $nhanVien = $taiKhoan->nhanVien;
        if (!$nhanVien) {
            return false;
        }

        $now = Carbon::now();
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $currentWeekEnd = $currentWeekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $cutoff = $currentWeekStart->copy()->addDays(3)->startOfDay(); // Thursday 00:00

        if ($now->lt($cutoff)) {
            return false;
        }

        $hasCurrent = $this->hasScheduleInRange($nhanVien->ID_NV, $currentWeekStart, $currentWeekEnd);
        $previousWeekStart = $currentWeekStart->copy()->subWeek();
        $previousWeekEnd = $previousWeekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $hasPrevious = $this->hasScheduleInRange($nhanVien->ID_NV, $previousWeekStart, $previousWeekEnd);

        return !$hasCurrent && !$hasPrevious;
    }

    private function hasScheduleInRange(string $staffId, Carbon $start, Carbon $end): bool
    {
        return LichLamViec::where('ID_NV', $staffId)
            ->whereBetween('NgayLam', [
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
            ])
            ->exists();
    }
}
