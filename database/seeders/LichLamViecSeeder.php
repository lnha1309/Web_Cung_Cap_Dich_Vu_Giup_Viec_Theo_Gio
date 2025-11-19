<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LichLamViecSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LichLamViec')->insert([
            [
                'ID_Lich' => 'LL001',
                'ID_NV' => 'NV001',
                'NgayLam' => '2025-11-18',
                'GioBatDau' => '08:00:00',
                'GioKetThuc' => '12:00:00',
                'TrangThai' => 'ready',
            ],
            [
                'ID_Lich' => 'LL002',
                'ID_NV' => 'NV001',
                'NgayLam' => '2025-11-18',
                'GioBatDau' => '13:00:00',
                'GioKetThuc' => '17:00:00',
                'TrangThai' => 'ready',
            ],
            [
                'ID_Lich' => 'LL003',
                'ID_NV' => 'NV001',
                'NgayLam' => '2025-11-19',
                'GioBatDau' => '08:00:00',
                'GioKetThuc' => '12:00:00',
                'TrangThai' => 'assigned',
            ],
            [
                'ID_Lich' => 'LL004',
                'ID_NV' => 'NV002',
                'NgayLam' => '2025-11-18',
                'GioBatDau' => '09:00:00',
                'GioKetThuc' => '13:00:00',
                'TrangThai' => 'ready',
            ],
            [
                'ID_Lich' => 'LL005',
                'ID_NV' => 'NV002',
                'NgayLam' => '2025-11-20',
                'GioBatDau' => '14:00:00',
                'GioKetThuc' => '18:00:00',
                'TrangThai' => 'ready',
            ],
        ]);
    }
}

