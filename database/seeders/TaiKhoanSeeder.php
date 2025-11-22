<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TaiKhoanSeeder extends Seeder
{
    public function run(): void
    {
        $hashedPassword = Hash::make('123456');

        DB::table('TaiKhoan')->insert([
            ['ID_TK' => 'TK001', 'TenDN' => 'user1', 'MatKhau' => $hashedPassword, 'ID_LoaiTK' => 'customer', 'TrangThaiTK' => 'active'],
            ['ID_TK' => 'TK002', 'TenDN' => 'user2', 'MatKhau' => $hashedPassword, 'ID_LoaiTK' => 'customer', 'TrangThaiTK' => 'active'],
            ['ID_TK' => 'TK003', 'TenDN' => 'staff1', 'MatKhau' => $hashedPassword, 'ID_LoaiTK' => 'staff', 'TrangThaiTK' => 'active'],
            ['ID_TK' => 'TK004', 'TenDN' => 'staff2', 'MatKhau' => $hashedPassword, 'ID_LoaiTK' => 'staff', 'TrangThaiTK' => 'active'],
            ['ID_TK' => 'TK005', 'TenDN' => 'admin1', 'MatKhau' => $hashedPassword, 'ID_LoaiTK' => 'admin', 'TrangThaiTK' => 'active'],
        ]);
    }
}
