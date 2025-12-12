<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm cột is_delete cho xoá mềm vào các bảng: DichVu, GoiThang, PhuThu, KhuyenMai
     */
    public function up(): void
    {
        // Thêm cột is_delete vào bảng DichVu
        Schema::table('DichVu', function (Blueprint $table) {
            $table->boolean('is_delete')->default(false)->after('ThoiLuong');
        });

        // Thêm cột is_delete vào bảng GoiThang
        Schema::table('GoiThang', function (Blueprint $table) {
            $table->boolean('is_delete')->default(false)->after('Mota');
        });

        // Thêm cột is_delete vào bảng PhuThu
        Schema::table('PhuThu', function (Blueprint $table) {
            $table->boolean('is_delete')->default(false)->after('GiaCuoc');
        });

        // Thêm cột is_delete vào bảng KhuyenMai
        Schema::table('KhuyenMai', function (Blueprint $table) {
            $table->boolean('is_delete')->default(false)->after('NgayHetHan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('DichVu', function (Blueprint $table) {
            $table->dropColumn('is_delete');
        });

        Schema::table('GoiThang', function (Blueprint $table) {
            $table->dropColumn('is_delete');
        });

        Schema::table('PhuThu', function (Blueprint $table) {
            $table->dropColumn('is_delete');
        });

        Schema::table('KhuyenMai', function (Blueprint $table) {
            $table->dropColumn('is_delete');
        });
    }
};
