<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichBuoiThangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LichBuoiThang')->insert([
            [
                'ID_Buoi' => 'LBT001',
                'ID_DD' => 'DD002',
                'NgayLam' => '2025-11-18',
                'GioBatDau' => '08:00:00',
                'TrangThaiBuoi' => 'finding_staff',
                'ID_NV' => 'NV002',
            ],
            [
                'ID_Buoi' => 'LBT002',
                'ID_DD' => 'DD002',
                'NgayLam' => '2025-11-20',
                'GioBatDau' => '08:00:00',
                'TrangThaiBuoi' => 'finding_staff',
                'ID_NV' => 'NV002',
            ],
            [
                'ID_Buoi' => 'LBT003',
                'ID_DD' => 'DD002',
                'NgayLam' => '2025-11-22',
                'GioBatDau' => '08:00:00',
                'TrangThaiBuoi' => 'completed',
                'ID_NV' => 'NV002',
            ],
        ]);
    }
}
