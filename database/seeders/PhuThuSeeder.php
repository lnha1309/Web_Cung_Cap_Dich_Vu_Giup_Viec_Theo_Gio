<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhuThuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('PhuThu')->insert([
            [
                'ID_PT' => 'PT001',
                'Ten_PT' => 'Phụ thu giờ cao điểm (trước 8h, sau 17h)',
                'GiaCuoc' => 30000,
            ],
            [
                'ID_PT' => 'PT002',
                'Ten_PT' => 'Phụ thu thú cưng (chó, mèo)',
                'GiaCuoc' => 30000,
            ],
            [
                'ID_PT' => 'PT003',
                'Ten_PT' => 'Phụ thu cuối tuần (T7, CN)',
                'GiaCuoc' => 30000
            ],
        ]);
    }
}

