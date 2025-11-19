<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChiTietPhuThuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ChiTietPhuThu')->insert([
            [
                'ID_PT' => 'PT001',
                'ID_DD' => 'DD001',
                'Ghichu' => 'Làm việc vào giờ sáng sớm',
            ],
            [
                'ID_PT' => 'PT002',
                'ID_DD' => 'DD002',
                'Ghichu' => 'Nhà có nuôi 2 con chó',
            ],
        ]);
    }
}

