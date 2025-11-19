<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichSuRutTienSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LichSuRutTien')->insert([
            [
                'ID_RutTien' => 'RT001',
                'ID_NV' => 'NV001',
                'SoTien' => 500000,
                'NgayRut' => '2025-11-01',
                'GioRut' => '10:00:00',
                'TrangThai' => 'approved',
                'GhiChu' => 'Rút tiền tháng 11',
            ],
        ]);
    }
}

