<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LichBuoiThang', function (Blueprint $table) {
            $table->string('ID_Buoi', 50)->primary();
            $table->string('ID_DD', 50);
            $table->date('NgayLam');
            $table->time('GioBatDau');
            $table->enum('TrangThaiBuoi', ['finding_staff', 'scheduled', 'completed', 'cancelled'])->default('finding_staff');
            $table->string('ID_NV', 50)->nullable();

            $table->foreign('ID_DD')
                ->references('ID_DD')
                ->on('DonDat');

            $table->foreign('ID_NV')
                ->references('ID_NV')
                ->on('NhanVien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LichBuoiThang');
    }
};
