<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DanhGiaNhanVien', function (Blueprint $table) {
            $table->string('ID_DG', 50)->primary();
            $table->string('ID_DD', 50);
            $table->string('ID_NV', 50);
            $table->string('ID_KH', 50);
            $table->decimal('Diem', 3, 2)->nullable();
            $table->text('NhanXet')->nullable();
            $table->dateTime('ThoiGian')->useCurrent();

            $table->foreign('ID_DD')
                ->references('ID_DD')
                ->on('DonDat');

            $table->foreign('ID_NV')
                ->references('ID_NV')
                ->on('NhanVien');

            $table->foreign('ID_KH')
                ->references('ID_KH')
                ->on('KhachHang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DanhGiaNhanVien');
    }
};

