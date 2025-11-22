<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('DonDat', function (Blueprint $table) {
        // 1. Drop foreign key
        $table->dropForeign('dondat_id_km_foreign'); // tên nó đang báo trong lỗi

        // 2. Drop luôn cột
        $table->dropColumn('ID_KM');
    });
}


    public function down(): void
    {
        Schema::table('DonDat', function (Blueprint $table) {
            if (!Schema::hasColumn('DonDat', 'ID_KM')) {
                $table->string('ID_KM')->nullable();
            }
        });
    }
};
