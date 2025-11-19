<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KhachHangSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('KhachHang')->insert([
            [
                'ID_KH' => 'KH001',
                'Ten_KH' => 'Nguyễn Văn A',
                'Email' => 'nguyenvana@example.com',
                'SDT' => '0909123456',
                'ID_TK' => 'TK001',
            ],
            [
                'ID_KH' => 'KH002',
                'Ten_KH' => 'Phạm Văn C',
                'Email' => 'phamvanc@example.com',
                'SDT' => '0908765432',
                'ID_TK' => 'TK002',
            ],
        ]);
    }
}

