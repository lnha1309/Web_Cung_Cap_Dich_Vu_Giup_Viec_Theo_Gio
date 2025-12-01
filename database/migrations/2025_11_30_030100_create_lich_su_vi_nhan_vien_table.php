<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LichSuViNhanVien', function (Blueprint $table) {
            $table->string('ID_LSV', 50)->primary();
            $table->string('ID_NV', 50);
            $table->enum('LoaiGiaoDich', [
                'topup',
                'cash_commission',
                'order_credit',
                'salary_payout',
                'adjustment',
            ])->default('adjustment');
            $table->enum('Huong', ['in', 'out'])->default('in');
            $table->decimal('SoTien', 15, 2);
            $table->decimal('SoDuSau', 15, 2)->nullable();
            $table->string('MoTa', 500)->nullable();
            $table->string('TrangThai', 50)->default('success');
            $table->string('ID_DD', 50)->nullable()->index();
            $table->string('Nguon', 50)->nullable();
            $table->string('MaThamChieu', 100)->nullable()->unique();
            $table->string('MaGiaoDich', 100)->nullable();
            $table->timestamps();

            $table->foreign('ID_NV')
                ->references('ID_NV')
                ->on('NhanVien')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LichSuViNhanVien');
    }
};
