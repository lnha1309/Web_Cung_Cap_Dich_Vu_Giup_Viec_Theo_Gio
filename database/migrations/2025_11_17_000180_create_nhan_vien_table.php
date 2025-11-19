<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('NhanVien', function (Blueprint $table) {
            $table->string('ID_NV', 50)->primary();
            $table->string('Ten_NV', 255)->nullable();
            $table->string('ID_Quan', 50)->nullable();
            $table->date('NgaySinh')->nullable();
            $table->enum('GioiTinh', ['male', 'female'])->nullable();
            $table->string('SDT', 15)->unique();
            $table->string('Email', 255)->unique()->nullable();
            $table->string('KhuVucLamViec', 255)->nullable();
            $table->decimal('SoDu', 15, 2)->nullable();
            $table->enum('TrangThai', ['active', 'not activated'])->default('not activated');
            $table->string('ID_TK', 50)->unique();

            $table->foreign('ID_TK')
                ->references('ID_TK')
                ->on('TaiKhoan');

            $table->foreign('ID_Quan')
                ->references('ID_Quan')
                ->on('Quan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NhanVien');
    }
};

