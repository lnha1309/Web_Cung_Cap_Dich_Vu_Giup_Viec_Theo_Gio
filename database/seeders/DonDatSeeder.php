<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DonDatSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('DonDat')->insert([
            [
                'ID_DD' => 'DD001',
                'LoaiDon' => 'hour',
                'ID_DV' => 'DV001',
                'ID_KH' => 'KH001',
                'ID_DC' => 'DC001',
                'GhiChu' => 'Làm sạch nhà bếp và phòng khách',
                'NgayLam' => '2025-11-20',
                'GioBatDau' => '08:00:00',
                'ThoiLuongGio' => 2,
                'ID_Goi' => null,
                'NgayBatDauGoi' => null,
                'NgayKetThucGoi' => null,
                'TrangThaiDon' => 'paid',
                'TongTien' => 192000,
                'TongTienSauGiam' => 153600,
                'ID_NV' => 'NV001',
            ],
            [
                'ID_DD' => 'DD002',
                'LoaiDon' => 'month',
                'ID_DV' => 'DV002',
                'ID_KH' => 'KH002',
                'ID_DC' => 'DC003',
                'GhiChu' => 'Gói tháng làm 3 lần/tuần',
                'NgayLam' => null,
                'GioBatDau' => null,
                'ThoiLuongGio' => null,
                'ID_Goi' => 'GT03',
                'NgayBatDauGoi' => '2025-11-01',
                'NgayKetThucGoi' => '2026-01-30',
                'TrangThaiDon' => 'assigned',
                'TongTien' => 7200000,
                'TongTienSauGiam' => 6120000,
                'ID_NV' => 'NV002',
            ],
        ]);
    }
}

