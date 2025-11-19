<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LichSuThanhToan', function (Blueprint $table) {
            $table->string('ID_LSTT', 50)->primary();
            $table->string('PhuongThucThanhToan', 50)->nullable();
            $table->enum('TrangThai', ['ThanhCong', 'ThatBai', 'ChoXuLy'])->default('ChoXuLy');
            $table->dateTime('ThoiGian')->useCurrent();
            $table->decimal('SoTienThanhToan', 12, 2)->nullable();
            $table->string('ID_DD', 50)->nullable();

            $table->foreign('ID_DD')
                ->references('ID_DD')
                ->on('DonDat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LichSuThanhToan');
    }
};

