<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LoaiTaiKhoan', function (Blueprint $table) {
            $table->string('ID_LoaiTK', 20)->primary();
            $table->string('TenLoai', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LoaiTaiKhoan');
    }
};

