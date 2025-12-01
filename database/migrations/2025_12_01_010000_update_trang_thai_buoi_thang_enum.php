<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('LichBuoiThang')) {
            return;
        }

        // Chuyển giá trị cũ 'scheduled' về 'finding_staff' trước khi đổi ENUM
        DB::table('LichBuoiThang')
            ->where('TrangThaiBuoi', 'scheduled')
            ->update(['TrangThaiBuoi' => 'finding_staff']);

        // Chuyển giá trị cũ 'assigned' (nếu có) về 'confirmed'
        DB::table('LichBuoiThang')
            ->where('TrangThaiBuoi', 'assigned')
            ->update(['TrangThaiBuoi' => 'confirmed']);

        // Enum hiện tại: finding_staff, scheduled, completed, cancelled
        // Cập nhật thành: finding_staff, confirmed, completed, cancelled
        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY `TrangThaiBuoi` ENUM('finding_staff','confirmed','completed','cancelled') DEFAULT 'finding_staff'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('LichBuoiThang')) {
            return;
        }

        DB::statement("ALTER TABLE `LichBuoiThang` MODIFY `TrangThaiBuoi` ENUM('finding_staff','scheduled','completed','cancelled') DEFAULT 'finding_staff'");
    }
};
