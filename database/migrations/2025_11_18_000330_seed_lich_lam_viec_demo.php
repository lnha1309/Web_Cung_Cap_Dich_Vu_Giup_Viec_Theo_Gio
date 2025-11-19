<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm lịch làm việc demo 7h-17h ngày 2025-11-20 cho NV001 và NV002 (nếu tồn tại)
        if (DB::table('NhanVien')->where('ID_NV', 'NV001')->exists()) {
            DB::table('LichLamViec')->updateOrInsert(
                ['ID_Lich' => 'LL_NV001_20251120'],
                [
                    'ID_NV'      => 'NV001',
                    'NgayLam'    => '2025-11-20',
                    'GioBatDau'  => '07:00:00',
                    'GioKetThuc' => '17:00:00',
                    'TrangThai'  => 'ready',
                ]
            );
        }

        if (DB::table('NhanVien')->where('ID_NV', 'NV002')->exists()) {
            DB::table('LichLamViec')->updateOrInsert(
                ['ID_Lich' => 'LL_NV002_20251120'],
                [
                    'ID_NV'      => 'NV002',
                    'NgayLam'    => '2025-11-20',
                    'GioBatDau'  => '07:00:00',
                    'GioKetThuc' => '17:00:00',
                    'TrangThai'  => 'ready',
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('LichLamViec')
            ->whereIn('ID_Lich', ['LL_NV001_20251120', 'LL_NV002_20251120'])
            ->delete();
    }
};

