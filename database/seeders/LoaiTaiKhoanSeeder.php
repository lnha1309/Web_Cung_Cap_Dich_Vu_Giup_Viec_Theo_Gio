<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoaiTaiKhoanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LoaiTaiKhoan')->insert([
            ['ID_LoaiTK' => 'customer', 'TenLoai' => 'Khách hàng'],
            ['ID_LoaiTK' => 'staff', 'TenLoai' => 'Nhân viên'],
            ['ID_LoaiTK' => 'admin', 'TenLoai' => 'Quản trị viên'],
        ]);
    }
}

