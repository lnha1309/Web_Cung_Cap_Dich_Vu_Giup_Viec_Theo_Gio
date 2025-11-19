<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiaChiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('DiaChi')->insert([
            [
                'ID_DC' => 'DC001',
                'ID_KH' => 'KH001',
                'ID_Quan' => 'Q01',
                'CanHo' => null,
                'DiaChiDayDu' => '123 Lê Lợi, Phường 1, Quận 1, TP.HCM',
            ],
            [
                'ID_DC' => 'DC002',
                'ID_KH' => 'KH001',
                'ID_Quan' => 'Q01',
                'CanHo' => null,
                'DiaChiDayDu' => '456 Nguyễn Huệ, Phường 2, Quận 1, TP.HCM',
            ],
            [
                'ID_DC' => 'DC003',
                'ID_KH' => 'KH002',
                'ID_Quan' => 'Q05',
                'CanHo' => null,
                'DiaChiDayDu' => '789 Võ Văn Tần, Phường 5, Quận 5, TP.HCM',
            ],
        ]);
    }
}

