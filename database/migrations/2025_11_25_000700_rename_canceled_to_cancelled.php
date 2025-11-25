<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `DonDat` MODIFY COLUMN `TrangThaiDon` ENUM('unpaid','paid','finding_staff','wait_confirm','assigned','confirmed','rejected','working','done','failed','canceled','cancelled') DEFAULT 'unpaid'");
        DB::statement("UPDATE `DonDat` SET `TrangThaiDon` = 'cancelled' WHERE `TrangThaiDon` = 'canceled'");
        DB::statement("ALTER TABLE `DonDat` MODIFY COLUMN `TrangThaiDon` ENUM('unpaid','paid','finding_staff','wait_confirm','assigned','confirmed','rejected','working','done','failed','cancelled') DEFAULT 'unpaid'");

        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY COLUMN `TrangThaiBuoi` ENUM('scheduled','completed','canceled','cancelled') DEFAULT 'scheduled'");
        DB::statement("UPDATE `LichBuoiThang` SET `TrangThaiBuoi` = 'cancelled' WHERE `TrangThaiBuoi` = 'canceled'");
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY COLUMN `TrangThaiBuoi` ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY COLUMN `TrangThaiBuoi` ENUM('scheduled','completed','canceled','cancelled') DEFAULT 'scheduled'");
        DB::statement("UPDATE `LichBuoiThang` SET `TrangThaiBuoi` = 'canceled' WHERE `TrangThaiBuoi` = 'cancelled'");
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY COLUMN `TrangThaiBuoi` ENUM('scheduled','completed','canceled') DEFAULT 'scheduled'");

        DB::statement("ALTER TABLE `DonDat` MODIFY COLUMN `TrangThaiDon` ENUM('unpaid','paid','finding_staff','wait_confirm','assigned','confirmed','rejected','working','done','failed','cancelled','canceled') DEFAULT 'unpaid'");
        DB::statement("UPDATE `DonDat` SET `TrangThaiDon` = 'canceled' WHERE `TrangThaiDon` = 'cancelled'");
        DB::statement("ALTER TABLE `DonDat` MODIFY COLUMN `TrangThaiDon` ENUM('unpaid','paid','finding_staff','wait_confirm','assigned','confirmed','rejected','working','done','failed','canceled') DEFAULT 'unpaid'");
    }
};
