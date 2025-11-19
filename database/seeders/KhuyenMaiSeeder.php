<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhuyenMaiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('KhuyenMai')->insert([
            [
                'ID_KM' => 'KM001',
                'Ten_KM' => 'Ưu đãi khách hàng mới',
                'MoTa' => 'Giảm 20% cho khách hàng đặt lịch giúp việc lần đầu qua hệ thống.',
                'PhanTramGiam' => 20.00,
                'GiamToiDa' => 100000,
                'NgayBatDau' => '2025-01-01',
                'NgayKetThuc' => '2025-03-31',
            ],
            [
                'ID_KM' => 'KM002',
                'Ten_KM' => 'Giờ vàng tiết kiệm',
                'MoTa' => 'Đặt dịch vụ trong khung 13h–17h từ thứ Hai đến thứ Sáu được giảm 15%.',
                'PhanTramGiam' => 15.00,
                'GiamToiDa' => 80000,
                'NgayBatDau' => '2025-02-01',
                'NgayKetThuc' => '2025-06-30',
            ],
            [
                'ID_KM' => 'KM003',
                'Ten_KM' => 'Cặp đôi cuối tuần',
                'MoTa' => 'Giảm giá cho khách đặt ít nhất 2 ca giúp việc vào cuối tuần.',
                'PhanTramGiam' => 25.00,
                'GiamToiDa' => 150000,
                'NgayBatDau' => '2025-04-01',
                'NgayKetThuc' => '2025-08-31',
            ],
            [
                'ID_KM' => 'KM004',
                'Ten_KM' => 'Tri ân khách hàng thân thiết',
                'MoTa' => 'Khách hàng đã sử dụng dịch vụ trên 10 lần sẽ được giảm tự động 10% cho lần kế tiếp.',
                'PhanTramGiam' => 10.00,
                'GiamToiDa' => 50000,
                'NgayBatDau' => '2025-05-01',
                'NgayKetThuc' => '2025-12-31',
            ],
            [
                'ID_KM' => 'KM005',
                'Ten_KM' => 'Làm sạch đón Tết',
                'MoTa' => 'Chương trình khuyến mãi đặc biệt dịp Tết cho tất cả gói dọn nhà theo giờ.',
                'PhanTramGiam' => 30.00,
                'GiamToiDa' => 200000,
                'NgayBatDau' => '2025-12-15',
                'NgayKetThuc' => '2026-02-15',
            ],
        ]);
    }
}

