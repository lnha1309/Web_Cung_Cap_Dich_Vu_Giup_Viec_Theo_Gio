<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichSuNhanDonSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LichSuNhanDon')->insert([
            [
                'ID_LSND' => 'LSND001',
                'ID_DD' => 'DD001',
                'ID_NV' => 'NV001',
                'HanhDong' => 'accept',
                'ThoiGian' => '2025-11-17 09:00:00',
            ],
            [
                'ID_LSND' => 'LSND002',
                'ID_DD' => 'DD002',
                'ID_NV' => 'NV002',
                'HanhDong' => 'self_assign',
                'ThoiGian' => '2025-11-01 15:00:00',
            ],
        ]);
    }
}

