<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ThongBao', function (Blueprint $table) {
            $table->string('ID_TB', 50)->primary();
            $table->string('ID_KH', 50);
            $table->string('TieuDe', 255);
            $table->text('NoiDung');
            $table->enum('LoaiThongBao', [
                'order_created',
                'order_cancelled', 
                'order_status_change',
                'refund_completed',
                'other'
            ]);
            $table->boolean('DaDoc')->default(false);
            $table->dateTime('ThoiGian')->useCurrent();
            $table->json('DuLieuLienQuan')->nullable();
            
            // Foreign key
            $table->foreign('ID_KH')
                ->references('ID_KH')
                ->on('KhachHang')
                ->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['ID_KH', 'DaDoc', 'ThoiGian'], 'idx_khachhang_daDoc_thoigian');
            $table->index(['ID_KH', 'LoaiThongBao'], 'idx_khachhang_loai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ThongBao');
    }
};
