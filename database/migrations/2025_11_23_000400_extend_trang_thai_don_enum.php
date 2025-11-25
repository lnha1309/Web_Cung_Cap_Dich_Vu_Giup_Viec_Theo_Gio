<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `DonDat`
            MODIFY COLUMN `TrangThaiDon` ENUM(
                'unpaid',
                'paid',
                'finding_staff',
                'wait_confirm',
                'assigned',
                'confirmed',
                'rejected',
                'done',
                'cancelled'
            ) DEFAULT 'unpaid'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `DonDat`
            MODIFY COLUMN `TrangThaiDon` ENUM(
                'unpaid',
                'paid',
                'finding_staff',
                'wait_confirm',
                'assigned',
                'done',
                'cancelled'
            ) DEFAULT 'unpaid'
        ");
    }
};
