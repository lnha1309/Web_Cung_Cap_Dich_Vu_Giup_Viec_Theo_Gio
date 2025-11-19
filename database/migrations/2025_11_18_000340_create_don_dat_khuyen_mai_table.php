<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('DonDat_KhuyenMai')) {
            Schema::create('DonDat_KhuyenMai', function (Blueprint $table) {
                $table->string('ID_DD', 50);
                $table->string('ID_KM', 50);
                $table->decimal('TienGiam', 12, 2);

                $table->primary(['ID_DD', 'ID_KM']);

                $table->foreign('ID_DD')
                    ->references('ID_DD')
                    ->on('DonDat')
                    ->onDelete('cascade');

                $table->foreign('ID_KM')
                    ->references('ID_KM')
                    ->on('KhuyenMai')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('DonDat_KhuyenMai')) {
            Schema::dropIfExists('DonDat_KhuyenMai');
        }
    }
};

