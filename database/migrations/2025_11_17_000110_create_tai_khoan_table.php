<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TaiKhoan', function (Blueprint $table) {
            $table->string('ID_TK', 50)->primary();
            $table->string('TenDN', 50)->unique();
            $table->string('MatKhau', 255);
            $table->string('ID_LoaiTK', 20)->default('customer');
            $table->enum('TrangThaiTK', ['active', 'banned'])->default('active');

            $table->foreign('ID_LoaiTK')
                ->references('ID_LoaiTK')
                ->on('LoaiTaiKhoan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TaiKhoan');
    }
};

