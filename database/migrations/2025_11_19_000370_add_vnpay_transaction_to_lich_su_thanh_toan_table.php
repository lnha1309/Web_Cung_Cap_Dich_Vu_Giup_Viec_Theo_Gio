<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('LichSuThanhToan', function (Blueprint $table) {
            if (!Schema::hasColumn('LichSuThanhToan', 'MaGiaoDichVNPAY')) {
                $table->string('MaGiaoDichVNPAY', 100)
                    ->nullable()
                    ->after('SoTienThanhToan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('LichSuThanhToan', function (Blueprint $table) {
            if (Schema::hasColumn('LichSuThanhToan', 'MaGiaoDichVNPAY')) {
                $table->dropColumn('MaGiaoDichVNPAY');
            }
        });
    }
};

