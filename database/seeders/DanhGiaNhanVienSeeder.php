<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DanhGiaNhanVienSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('DanhGiaNhanVien')->insert([
            [
                'ID_DG' => 'DG001',
                'ID_DD' => 'DD001',
                'ID_NV' => 'NV001',
                'ID_KH' => 'KH001',
                'Diem' => 4.50,
                'NhanXet' => 'Nhân viên làm việc nhanh và sạch sẽ.',
                'ThoiGian' => '2025-11-20 18:00:00',
            ],
            [
                'ID_DG' => 'DG002',
                'ID_DD' => 'DD002',
                'ID_NV' => 'NV002',
                'ID_KH' => 'KH002',
                'Diem' => 5.00,
                'NhanXet' => 'Rất hài lòng với dịch vụ, sẽ tiếp tục sử dụng.',
                'ThoiGian' => '2025-11-15 20:30:00',
            ],
        ]);
    }
}

