<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('Quan')->insert([
            ['ID_Quan' => 'Q01', 'TenQuan' => 'Quận 1', 'ViDo' => 10.776889, 'KinhDo' => 106.700806],
            ['ID_Quan' => 'Q02', 'TenQuan' => 'Quận 2', 'ViDo' => 10.773178, 'KinhDo' => 106.761006],
            ['ID_Quan' => 'Q03', 'TenQuan' => 'Quận 3', 'ViDo' => 10.779836, 'KinhDo' => 106.689529],
            ['ID_Quan' => 'Q04', 'TenQuan' => 'Quận 4', 'ViDo' => 10.763157, 'KinhDo' => 106.692779],
            ['ID_Quan' => 'Q05', 'TenQuan' => 'Quận 5', 'ViDo' => 10.753194, 'KinhDo' => 106.664839],
            ['ID_Quan' => 'Q06', 'TenQuan' => 'Quận 6', 'ViDo' => 10.746619, 'KinhDo' => 106.641517],
            ['ID_Quan' => 'Q07', 'TenQuan' => 'Quận 7', 'ViDo' => 10.728527, 'KinhDo' => 106.720735],
            ['ID_Quan' => 'Q08', 'TenQuan' => 'Quận 8', 'ViDo' => 10.739672, 'KinhDo' => 106.647848],
            ['ID_Quan' => 'Q09', 'TenQuan' => 'TP Thủ Đức', 'ViDo' => 10.831602, 'KinhDo' => 106.795218],
            ['ID_Quan' => 'Q10', 'TenQuan' => 'Quận 10', 'ViDo' => 10.765454, 'KinhDo' => 106.666683],
            ['ID_Quan' => 'Q11', 'TenQuan' => 'Quận 11', 'ViDo' => 10.767691, 'KinhDo' => 106.648103],
            ['ID_Quan' => 'Q12', 'TenQuan' => 'Quận 12', 'ViDo' => 10.870793, 'KinhDo' => 106.642052],
            ['ID_Quan' => 'QBT', 'TenQuan' => 'Quận Bình Thạnh', 'ViDo' => 10.808091, 'KinhDo' => 106.711793],
            ['ID_Quan' => 'QGV', 'TenQuan' => 'Quận Gò Vấp', 'ViDo' => 10.831471, 'KinhDo' => 106.666842],
            ['ID_Quan' => 'QTP', 'TenQuan' => 'Quận Tân Phú', 'ViDo' => 10.794081, 'KinhDo' => 106.616575],
            ['ID_Quan' => 'QTB', 'TenQuan' => 'Quận Thủ Đức', 'ViDo' => 10.852305, 'KinhDo' => 106.763381],
            ['ID_Quan' => 'HCN', 'TenQuan' => 'Huyện Củ Chi', 'ViDo' => 11.001717, 'KinhDo' => 106.536056],
            ['ID_Quan' => 'HNB', 'TenQuan' => 'Huyện Hóc Môn', 'ViDo' => 10.856883, 'KinhDo' => 106.592059],
            ['ID_Quan' => 'HTN', 'TenQuan' => 'Huyện Nhà Bè', 'ViDo' => 10.695733, 'KinhDo' => 106.743663],
            ['ID_Quan' => 'HPN', 'TenQuan' => 'Huyện Bình Chánh', 'ViDo' => 10.697263, 'KinhDo' => 106.635775],
            ['ID_Quan' => 'HCG', 'TenQuan' => 'Huyện Cần Giờ', 'ViDo' => 10.439554, 'KinhDo' => 106.868366],
        ]);
    }
}

