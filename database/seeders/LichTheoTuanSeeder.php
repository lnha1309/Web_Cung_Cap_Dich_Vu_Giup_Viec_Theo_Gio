<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichTheoTuanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LichTheoTuan')->insert([
            ['ID_LichTuan' => 'LTT001', 'ID_DD' => 'DD002', 'Thu' => 2, 'GioBatDau' => '08:00:00'],
            ['ID_LichTuan' => 'LTT002', 'ID_DD' => 'DD002', 'Thu' => 4, 'GioBatDau' => '08:00:00'],
            ['ID_LichTuan' => 'LTT003', 'ID_DD' => 'DD002', 'Thu' => 6, 'GioBatDau' => '08:00:00'],
        ]);
    }
}

