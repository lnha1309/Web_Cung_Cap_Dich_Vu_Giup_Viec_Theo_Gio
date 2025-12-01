<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('LichBuoiThang')) {
            return;
        }

        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY `TrangThaiBuoi` ENUM('finding_staff','assigned','confirmed','completed','cancelled','rejected') DEFAULT 'finding_staff'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('LichBuoiThang')) {
            return;
        }

        DB::table('LichBuoiThang')
            ->where('TrangThaiBuoi', 'rejected')
            ->update(['TrangThaiBuoi' => 'finding_staff']);

        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY `TrangThaiBuoi` ENUM('finding_staff','assigned','confirmed','completed','cancelled') DEFAULT 'finding_staff'");
    }
};
