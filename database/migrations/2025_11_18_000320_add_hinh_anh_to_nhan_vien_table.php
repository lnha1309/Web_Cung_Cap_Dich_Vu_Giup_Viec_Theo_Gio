<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('NhanVien', 'HinhAnh')) {
            Schema::table('NhanVien', function (Blueprint $table) {
                $table->string('HinhAnh', 1000)->nullable()->after('KhuVucLamViec');
            });
        }

        // Gán thử ảnh demo cho các nhân viên hiện có
        if (Schema::hasColumn('NhanVien', 'HinhAnh')) {
            DB::table('NhanVien')
                ->where('ID_NV', 'NV001')
                ->update([
                    'HinhAnh' => 'https://i.pravatar.cc/150?img=5',
                ]);

            DB::table('NhanVien')
                ->where('ID_NV', 'NV002')
                ->update([
                    'HinhAnh' => 'https://i.pravatar.cc/150?img=9',
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('NhanVien', 'HinhAnh')) {
            Schema::table('NhanVien', function (Blueprint $table) {
                $table->dropColumn('HinhAnh');
            });
        }
    }
};

