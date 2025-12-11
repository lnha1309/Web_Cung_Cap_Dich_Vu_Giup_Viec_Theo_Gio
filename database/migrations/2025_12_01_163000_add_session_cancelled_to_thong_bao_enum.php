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
        // Keep existing enum values to avoid truncating old rows when adding the new option
        DB::statement("ALTER TABLE `ThongBao` MODIFY COLUMN `LoaiThongBao` ENUM('order_created', 'order_cancelled', 'order_status_change', 'refund_completed', 'finding_staff_delay', 'order_rescheduled', 'other', 'session_cancelled') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `ThongBao` MODIFY COLUMN `LoaiThongBao` ENUM('order_created', 'order_cancelled', 'order_status_change', 'refund_completed', 'finding_staff_delay', 'order_rescheduled', 'other') NOT NULL");
    }
};
