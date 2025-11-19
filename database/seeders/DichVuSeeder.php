<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DichVuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('DichVu')->insert([
            [
                'ID_DV' => 'DV001',
                'TenDV' => 'Giúp việc nhà gói nhỏ',
                'MoTa' => 'Giúp việc tối đa 2 phòng, 2 giờ cho diện tích ≤ 55 m²',
                'GiaDV' => 192000,
                'DienTichToiDa' => 55.00,
                'SoPhong' => 2,
                'ThoiLuong' => 2.00,
            ],
            [
                'ID_DV' => 'DV002',
                'TenDV' => 'Giúp việc nhà gói tiêu chuẩn',
                'MoTa' => 'Giúp việc tối đa 3 phòng, 3 giờ cho diện tích ≤ 85 m²',
                'GiaDV' => 240000,
                'DienTichToiDa' => 85.00,
                'SoPhong' => 3,
                'ThoiLuong' => 3.00,
            ],
            [
                'ID_DV' => 'DV003',
                'TenDV' => 'Giúp việc nhà gói lớn',
                'MoTa' => 'Giúp việc tối đa 4 phòng, 4 giờ cho diện tích ≤ 105 m²',
                'GiaDV' => 320000,
                'DienTichToiDa' => 105.00,
                'SoPhong' => 4,
                'ThoiLuong' => 4.00,
            ],
        ]);
    }
}

