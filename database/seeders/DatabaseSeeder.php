<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LoaiTaiKhoanSeeder::class,
            TaiKhoanSeeder::class,
            KhachHangSeeder::class,
            KhuyenMaiSeeder::class,
            DichVuSeeder::class,
            GoiThangSeeder::class,
            QuanSeeder::class,
            DiaChiSeeder::class,
            NhanVienSeeder::class,
            PhuThuSeeder::class,
            DonDatSeeder::class,
            LichSuThanhToanSeeder::class,
            ChiTietPhuThuSeeder::class,
            LichLamViecSeeder::class,
            LichSuRutTienSeeder::class,
            LichBuoiThangSeeder::class,
            LichTheoTuanSeeder::class,
            LichSuNhanDonSeeder::class,
            DanhGiaNhanVienSeeder::class,
        ]);
    }
}
