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
        // Modify ENUM to add 'reschedule_surcharge' value
        DB::statement("ALTER TABLE LichSuThanhToan MODIFY COLUMN LoaiGiaoDich ENUM('payment', 'refund', 'reschedule_surcharge') DEFAULT 'payment'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM values
        DB::statement("ALTER TABLE LichSuThanhToan MODIFY COLUMN LoaiGiaoDich ENUM('payment', 'refund') DEFAULT 'payment'");
    }
};
