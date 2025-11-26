<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY COLUMN `TrangThaiBuoi` ENUM('finding_staff','scheduled','completed','canceled','cancelled') DEFAULT 'finding_staff'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY COLUMN `TrangThaiBuoi` ENUM('scheduled','completed','canceled','cancelled') DEFAULT 'scheduled'");
    }
};
