<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NEW_ENUM = "ENUM('order_created','order_cancelled','order_status_change','refund_completed','finding_staff_delay','order_rescheduled','other')";
    private const OLD_ENUM = "ENUM('order_created','order_cancelled','order_status_change','refund_completed','other')";

    public function up(): void
    {
        DB::statement("ALTER TABLE `ThongBao` MODIFY COLUMN `LoaiThongBao` " . self::NEW_ENUM);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `ThongBao` MODIFY COLUMN `LoaiThongBao` " . self::OLD_ENUM);
    }
};
