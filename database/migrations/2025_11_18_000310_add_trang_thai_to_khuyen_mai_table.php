<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('KhuyenMai', 'TrangThai')) {
            Schema::table('KhuyenMai', function (Blueprint $table) {
                $table->enum('TrangThai', ['activated', 'deactivated'])
                    ->default('activated');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('KhuyenMai', 'TrangThai')) {
            Schema::table('KhuyenMai', function (Blueprint $table) {
                $table->dropColumn('TrangThai');
            });
        }
    }
};
