<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoiThangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('GoiThang')->insert([
            ['ID_Goi' => 'GT01', 'TenGoi' => 'Gói 1 tháng', 'SoNgay' => 30, 'PhanTramGiam' => 5.00, 'Mota' => 'Giảm 5% cho gói 1 tháng.'],
            ['ID_Goi' => 'GT02', 'TenGoi' => 'Gói 2 tháng', 'SoNgay' => 60, 'PhanTramGiam' => 10.00, 'Mota' => 'Giảm 10% cho gói 2 tháng.'],
            ['ID_Goi' => 'GT03', 'TenGoi' => 'Gói 3 tháng', 'SoNgay' => 90, 'PhanTramGiam' => 15.00, 'Mota' => 'Giảm 15% cho gói 3 tháng.'],
            ['ID_Goi' => 'GT04', 'TenGoi' => 'Gói 6 tháng', 'SoNgay' => 180, 'PhanTramGiam' => 20.00, 'Mota' => 'Giảm 20% cho gói 6 tháng.'],
        ]);
    }
}

