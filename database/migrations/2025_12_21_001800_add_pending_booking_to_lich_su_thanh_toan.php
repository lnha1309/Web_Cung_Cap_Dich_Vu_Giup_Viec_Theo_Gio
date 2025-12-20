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
        // Modify ENUM to add 'pending_booking' value for orders waiting VNPay payment
        DB::statement("ALTER TABLE LichSuThanhToan MODIFY COLUMN LoaiGiaoDich ENUM('payment', 'refund', 'reschedule_surcharge', 'pending_booking') DEFAULT 'payment'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 
        DB::statement("ALTER TABLE LichSuThanhToan MODIFY COLUMN LoaiGiaoDich ENUM('payment', 'refund', 'reschedule_surcharge') DEFAULT 'payment'");
    }
};
