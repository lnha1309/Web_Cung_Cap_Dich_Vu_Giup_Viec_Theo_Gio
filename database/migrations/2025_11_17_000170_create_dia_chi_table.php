<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DiaChi', function (Blueprint $table) {
            $table->string('ID_DC', 50)->primary();
            $table->string('ID_KH', 50);
            $table->string('ID_Quan', 50)->nullable();
            $table->text('CanHo')->nullable();
            $table->text('DiaChiDayDu')->nullable();

            $table->foreign('ID_KH')
                ->references('ID_KH')
                ->on('KhachHang');

            $table->foreign('ID_Quan')
                ->references('ID_Quan')
                ->on('Quan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DiaChi');
    }
};

