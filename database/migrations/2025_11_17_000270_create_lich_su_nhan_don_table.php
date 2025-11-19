<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LichSuNhanDon', function (Blueprint $table) {
            $table->string('ID_LSND', 50)->primary();
            $table->string('ID_DD', 50);
            $table->string('ID_NV', 50);
            $table->enum('HanhDong', ['accept', 'reject', 'self_assign']);
            $table->dateTime('ThoiGian')->useCurrent();

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
        Schema::dropIfExists('LichSuNhanDon');
    }
};

