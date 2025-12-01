<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the ENUM column as Doctrine DBAL has issues with ENUMs
        DB::statement("ALTER TABLE `ThongBao` MODIFY COLUMN `LoaiThongBao` ENUM('order_created', 'order_cancelled', 'order_status_change', 'refund_completed', 'other', 'session_cancelled') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `ThongBao` MODIFY COLUMN `LoaiThongBao` ENUM('order_created', 'order_cancelled', 'order_status_change', 'refund_completed', 'other') NOT NULL");
    }
};
