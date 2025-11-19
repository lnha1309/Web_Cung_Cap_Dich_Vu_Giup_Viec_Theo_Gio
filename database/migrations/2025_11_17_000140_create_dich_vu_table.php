<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DichVu', function (Blueprint $table) {
            $table->string('ID_DV', 50)->primary();
            $table->string('TenDV', 255);
            $table->text('MoTa')->nullable();
            $table->decimal('GiaDV', 15, 2);
            $table->decimal('DienTichToiDa', 10, 2)->nullable();
            $table->integer('SoPhong')->nullable();
            $table->decimal('ThoiLuong', 4, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DichVu');
    }
};

