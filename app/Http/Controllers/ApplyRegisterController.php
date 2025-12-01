<?php

namespace App\Http\Controllers;

use App\Models\TaiKhoan;
use App\Support\IdGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplyRegisterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'username'   => ['required', 'string', 'min:4', 'max:50', 'unique:TaiKhoan,TenDN'],
                'password'   => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}$/',
                ],
                'full_name'  => ['required', 'string', 'max:255'],
                'email'      => ['required', 'email', 'max:255', 'unique:NhanVien,Email', 'unique:TaiKhoan,email'],
                'phone'      => [
                    'required',
                    'string',
                    'max:15',
                    'regex:/^(0|\\+84)[3|5|7|8|9][0-9]{8}$/',
                    'unique:NhanVien,SDT',
                ],
                'gender'     => ['nullable', 'string', 'max:20'],
                'dob'        => ['nullable', 'date'],
                'khu_vuc'    => ['nullable', 'string', 'max:255'],
            ],
            [
                'username.unique' => 'Tên đăng nhập đã tồn tại.',
                'password.regex'  => 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường và ký tự đặc biệt.',
                'email.unique'    => 'Email đã tồn tại trong hệ thống.',
                'phone.unique'    => 'Số điện thoại đã tồn tại trong hệ thống.',
                'phone.regex'     => 'Số điện thoại không hợp lệ.',
            ],
            [
                'username'  => 'Tên đăng nhập',
                'password'  => 'Mật khẩu',
                'full_name' => 'Họ tên',
                'email'     => 'Email',
                'phone'     => 'Số điện thoại',
                'gender'    => 'Giới tính',
                'dob'       => 'Ngày sinh',
                'khu_vuc'   => 'Khu vực',
            ]
        );

        $idTk = IdGenerator::next('TaiKhoan', 'ID_TK', 'TK_');

        TaiKhoan::create([
            'ID_TK'       => $idTk,
            'TenDN'       => $data['username'],
            'MatKhau'     => $data['password'],
            'ID_LoaiTK'   => 'staff',
            'TrangThaiTK' => 'inactive',
            'email'       => $data['email'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo tài khoản ứng viên (inactive), chờ admin duyệt hồ sơ và thêm vào bảng nhân viên.',
        ]);
    }
}
