<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('DonDat', function (Blueprint $table) {
            // Add composite index to optimize auto-cancel event query
            $table->index(['TrangThaiDon', 'NgayLam', 'GioBatDau'], 'idx_dondat_auto_cancel');
        });

        Schema::table('LichBuoiThang', function (Blueprint $table) {
            // Add composite index for monthly order auto-cancel logic
            $table->index(['ID_DD', 'NgayLam', 'GioBatDau', 'TrangThaiBuoi'], 'idx_lichbuoi_auto_cancel');
        });
    }

    public function down(): void
    {
        Schema::table('DonDat', function (Blueprint $table) {
            $table->dropIndex('idx_dondat_auto_cancel');
        });

        Schema::table('LichBuoiThang', function (Blueprint $table) {
            $table->dropIndex('idx_lichbuoi_auto_cancel');
        });
    }
};
