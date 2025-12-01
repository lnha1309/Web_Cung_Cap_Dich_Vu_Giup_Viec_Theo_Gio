<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `DonDat` MODIFY COLUMN `TrangThaiDon` ENUM('finding_staff','assigned','confirmed','rejected','completed','cancelled')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `DonDat` MODIFY COLUMN `TrangThaiDon` ENUM('finding_staff','assigned','confirmed','rejected','completed','cancelled')");
    }
};
