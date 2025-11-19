<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('DonDat_KhuyenMai') && !Schema::hasTable('ChiTietKhuyenMai')) {
            Schema::rename('DonDat_KhuyenMai', 'ChiTietKhuyenMai');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ChiTietKhuyenMai') && !Schema::hasTable('DonDat_KhuyenMai')) {
            Schema::rename('ChiTietKhuyenMai', 'DonDat_KhuyenMai');
        }
    }
};

