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

        // Thêm 'assigned' vào enum (giữ các giá trị đang có)
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY `TrangThaiBuoi` ENUM('finding_staff','assigned','confirmed','completed','cancelled') DEFAULT 'finding_staff'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('LichBuoiThang')) {
            return;
        }

        // Quay về enum trước đó không có assigned
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY `TrangThaiBuoi` ENUM('finding_staff','confirmed','completed','cancelled') DEFAULT 'finding_staff'");
    }
};
