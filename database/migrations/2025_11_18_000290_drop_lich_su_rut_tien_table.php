<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('LichSuRutTien');
    }

    public function down(): void
    {
        Schema::create('LichSuRutTien', function (Blueprint $table) {
            $table->string('ID_RutTien', 50)->primary();
            $table->string('ID_NV', 50);
            $table->decimal('SoTien', 15, 2);
            $table->date('NgayRut');
            $table->time('GioRut');
            $table->enum('TrangThai', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('GhiChu', 255)->nullable();

            $table->foreign('ID_NV')
                ->references('ID_NV')
                ->on('NhanVien');
        });
    }
};

