<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('KhuyenMai', function (Blueprint $table) {
            if (Schema::hasColumn('KhuyenMai', 'NgayBatDau')) {
                $table->dropColumn('NgayBatDau');
            }
            if (Schema::hasColumn('KhuyenMai', 'NgayKetThuc')) {
                $table->dropColumn('NgayKetThuc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('KhuyenMai', function (Blueprint $table) {
            if (!Schema::hasColumn('KhuyenMai', 'NgayBatDau')) {
                $table->date('NgayBatDau')->nullable();
            }
            if (!Schema::hasColumn('KhuyenMai', 'NgayKetThuc')) {
                $table->date('NgayKetThuc')->nullable();
            }
        });
    }
};

