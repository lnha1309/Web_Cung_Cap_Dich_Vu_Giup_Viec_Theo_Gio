<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DonDat', function (Blueprint $table) {
            $table->string('ID_DD', 50)->primary();
            $table->enum('LoaiDon', ['hour', 'month']);
            $table->string('ID_DV', 50);
            $table->string('ID_KH', 50)->nullable();
            $table->string('ID_DC', 50)->nullable();
            $table->text('GhiChu')->nullable();
            $table->dateTime('NgayTao')->useCurrent();
            $table->date('NgayLam')->nullable();
            $table->time('GioBatDau')->nullable();
            $table->integer('ThoiLuongGio')->nullable();
            $table->string('ID_Goi', 50)->nullable();
            $table->date('NgayBatDauGoi')->nullable();
            $table->date('NgayKetThucGoi')->nullable();
            $table->enum('TrangThaiDon', ['finding_staff', 'assigned', 'completed', 'cancelled','rejected']);
            $table->decimal('TongTien', 12, 2)->nullable();
            $table->decimal('TongTienSauGiam', 12, 2)->nullable();
            $table->string('ID_NV', 50)->nullable();
            $table->string('ID_KM', 50)->nullable();

            $table->foreign('ID_KH')
                ->references('ID_KH')
                ->on('KhachHang');

            $table->foreign('ID_DC')
                ->references('ID_DC')
                ->on('DiaChi');

            $table->foreign('ID_DV')
                ->references('ID_DV')
                ->on('DichVu');

            $table->foreign('ID_Goi')
                ->references('ID_Goi')
                ->on('GoiThang');

            $table->foreign('ID_NV')
                ->references('ID_NV')
                ->on('NhanVien');

            $table->foreign('ID_KM')
                ->references('ID_KM')
                ->on('KhuyenMai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DonDat');
    }
};
