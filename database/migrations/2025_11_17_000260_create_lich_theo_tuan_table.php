<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LichTheoTuan', function (Blueprint $table) {
            $table->string('ID_LichTuan', 50)->primary();
            $table->string('ID_DD', 50);
            $table->tinyInteger('Thu');
            $table->time('GioBatDau');

            $table->foreign('ID_DD')
                ->references('ID_DD')
                ->on('DonDat')
                ->onDelete('cascade');

            $table->unique(['ID_DD', 'Thu'], 'unique_don_thu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LichTheoTuan');
    }
};

