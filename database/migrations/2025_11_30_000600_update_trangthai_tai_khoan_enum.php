<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('TaiKhoan')) {
            return;
        }

        DB::statement("ALTER TABLE `TaiKhoan` MODIFY `TrangThaiTK` ENUM('active','banned','inactive') NOT NULL DEFAULT 'inactive'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('TaiKhoan')) {
            return;
        }

        DB::statement("ALTER TABLE `TaiKhoan` MODIFY `TrangThaiTK` ENUM('active','banned','locked') NOT NULL DEFAULT 'active'");
    }
};
