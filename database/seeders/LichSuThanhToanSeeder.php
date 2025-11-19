<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichSuThanhToanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LichSuThanhToan')->insert([
            [
                'ID_LSTT' => 'LSTT001',
                'PhuongThucThanhToan' => 'VNPay',
                'TrangThai' => 'ThanhCong',
                'ThoiGian' => '2025-11-17 10:30:00',
                'SoTienThanhToan' => 153600,
                'ID_DD' => 'DD001',
            ],
            [
                'ID_LSTT' => 'LSTT002',
                'PhuongThucThanhToan' => 'Momo',
                'TrangThai' => 'ThanhCong',
                'ThoiGian' => '2025-11-01 14:20:00',
                'SoTienThanhToan' => 6120000,
                'ID_DD' => 'DD002',
            ],
        ]);
    }
}

