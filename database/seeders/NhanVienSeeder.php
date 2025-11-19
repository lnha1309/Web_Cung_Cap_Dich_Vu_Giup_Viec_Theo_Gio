<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NhanVienSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('NhanVien')->insert([
            [
                'ID_NV' => 'NV001',
                'Ten_NV' => 'Trần Thị B',
                'ID_Quan' => 'Q01',
                'NgaySinh' => '1990-05-12',
                'GioiTinh' => 'female',
                'SDT' => '0912345678',
                'Email' => 'tranthib@example.com',
                'KhuVucLamViec' => 'Quận 1, Quận 3',
                'SoDu' => 1500000,
                'TrangThai' => 'active',
                'ID_TK' => 'TK003',
            ],
            [
                'ID_NV' => 'NV002',
                'Ten_NV' => 'Lê Thị D',
                'ID_Quan' => 'Q05',
                'NgaySinh' => '1995-08-20',
                'GioiTinh' => 'female',
                'SDT' => '0923456789',
                'Email' => 'lethid@example.com',
                'KhuVucLamViec' => 'Quận 5, Quận 10',
                'SoDu' => 800000,
                'TrangThai' => 'active',
                'ID_TK' => 'TK004',
            ],
        ]);
    }
}

