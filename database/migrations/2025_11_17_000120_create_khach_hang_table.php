<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('KhachHang', function (Blueprint $table) {
            $table->string('ID_KH', 50)->primary();
            $table->string('Ten_KH', 255);
            $table->string('Email', 255)->unique()->nullable();
            $table->string('SDT', 15)->unique();
            $table->string('ID_TK', 50)->unique();

            $table->foreign('ID_TK')
                ->references('ID_TK')
                ->on('TaiKhoan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KhachHang');
    }
};

