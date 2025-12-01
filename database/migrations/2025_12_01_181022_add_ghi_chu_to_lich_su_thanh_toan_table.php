<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('LichSuThanhToan', function (Blueprint $table) {
            $table->string('GhiChu', 255)->nullable()->after('LyDoHoanTien');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('LichSuThanhToan', function (Blueprint $table) {
            $table->dropColumn('GhiChu');
        });
    }
};
