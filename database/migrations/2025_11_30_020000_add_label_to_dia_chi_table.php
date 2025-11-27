<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('DiaChi', function (Blueprint $table) {
            if (!Schema::hasColumn('DiaChi', 'Nhan')) {
                $table->string('Nhan', 100)->nullable()->after('DiaChiDayDu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('DiaChi', function (Blueprint $table) {
            if (Schema::hasColumn('DiaChi', 'Nhan')) {
                $table->dropColumn('Nhan');
            }
        });
    }
};
