<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('DiaChi', function (Blueprint $table) {
            if (!Schema::hasColumn('DiaChi', 'is_Deleted')) {
                $table->boolean('is_Deleted')
                    ->default(false)
                    ->after('DiaChiDayDu');
            }
        });

        // Đảm bảo toàn bộ dữ liệu hiện tại đều có giá trị mặc định FALSE
        if (Schema::hasColumn('DiaChi', 'is_Deleted')) {
            DB::table('DiaChi')->update(['is_Deleted' => false]);
        }
    }

    public function down(): void
    {
        Schema::table('DiaChi', function (Blueprint $table) {
            if (Schema::hasColumn('DiaChi', 'is_Deleted')) {
                $table->dropColumn('is_Deleted');
            }
        });
    }
};

