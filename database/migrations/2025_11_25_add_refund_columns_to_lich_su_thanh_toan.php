<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('LichSuThanhToan', function (Blueprint $table) {
            // Loại giao dịch: thanh toán hoặc hoàn tiền
            $table->enum('LoaiGiaoDich', ['payment', 'refund'])->default('payment')->after('TrangThai');
            
            // Lý do hoàn tiền (nếu là refund)
            $table->string('LyDoHoanTien', 255)->nullable()->after('LoaiGiaoDich');
            
            // Tham chiếu đến giao dịch gốc (nếu là refund)
            $table->string('MaGiaoDichGoc', 255)->nullable()->after('LyDoHoanTien');
            
            // Thêm index cho query performance
            $table->index(['ID_DD', 'LoaiGiaoDich'], 'idx_dondat_loaigd');
        });
    }

    public function down(): void
    {
        Schema::table('LichSuThanhToan', function (Blueprint $table) {
            $table->dropIndex('idx_dondat_loaigd');
            $table->dropColumn(['LoaiGiaoDich', 'LyDoHoanTien', 'MaGiaoDichGoc']);
        });
    }
};
