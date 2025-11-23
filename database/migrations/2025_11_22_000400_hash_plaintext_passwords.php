<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('TaiKhoan')
            ->select('ID_TK', 'MatKhau')
            ->orderBy('ID_TK')
            ->chunk(100, function ($accounts) {
                foreach ($accounts as $account) {
                    $hashInfo = Hash::info($account->MatKhau);
                    $needsHash = ($hashInfo['algoName'] ?? 'unknown') === 'unknown'
                        || Hash::needsRehash($account->MatKhau);

                    if (!$needsHash) {
                        continue; // already hashed
                    }

                    DB::table('TaiKhoan')
                        ->where('ID_TK', $account->ID_TK)
                        ->update([
                            'MatKhau' => Hash::make($account->MatKhau),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Not reversible because plaintext passwords are not stored.
    }
};
