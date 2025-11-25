<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('TaiKhoan', function (Blueprint $table) {
            if (!Schema::hasColumn('TaiKhoan', 'name')) {
                $table->string('name')->nullable()->after('TenDN');
            }
            if (!Schema::hasColumn('TaiKhoan', 'email')) {
                $table->string('email')->nullable()->after('name');
            }
            if (!Schema::hasColumn('TaiKhoan', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (!Schema::hasColumn('TaiKhoan', 'onesignal_player_id')) {
                $table->string('onesignal_player_id', 191)->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('TaiKhoan', 'remember_token')) {
                $table->rememberToken()->after('onesignal_player_id');
            }
            if (!Schema::hasColumn('TaiKhoan', 'created_at')) {
                $table->timestamps();
            }
        });

        if (Schema::hasTable('personal_access_tokens')) {
            try {
                DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id VARCHAR(50)');
            } catch (\Throwable $e) {
                // ignore if the column is already the correct type
            }

            $legacyTokens = DB::table('personal_access_tokens')
                ->where('tokenable_type', \App\Models\User::class)
                ->get();

            foreach ($legacyTokens as $token) {
                $idTk = null;
                if (Schema::hasTable('users')) {
                    $idTk = DB::table('users')
                        ->where('id', $token->tokenable_id)
                        ->value('id_tk');
                }

                if ($idTk) {
                    DB::table('personal_access_tokens')
                        ->where('id', $token->id)
                        ->update([
                            'tokenable_id' => $idTk,
                            'tokenable_type' => \App\Models\TaiKhoan::class,
                        ]);
                }
            }

            DB::table('personal_access_tokens')
                ->where('tokenable_type', \App\Models\User::class)
                ->update(['tokenable_type' => \App\Models\TaiKhoan::class]);
        }

        if (Schema::hasTable('users')) {
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                DB::table('TaiKhoan')
                    ->where('ID_TK', $user->id_tk)
                    ->update([
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                        'onesignal_player_id' => $user->onesignal_player_id ?? null,
                        'remember_token' => $user->remember_token,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]);
            }

            Schema::dropIfExists('users');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('id_tk', 50)->unique();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->string('onesignal_player_id', 191)->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('TaiKhoan')) {
            $accounts = DB::table('TaiKhoan')->get();
            foreach ($accounts as $account) {
                DB::table('users')->updateOrInsert(
                    ['id_tk' => $account->ID_TK],
                    [
                        'name' => $account->name,
                        'email' => $account->email,
                        'email_verified_at' => $account->email_verified_at,
                        'onesignal_player_id' => $account->onesignal_player_id,
                        'remember_token' => $account->remember_token,
                        'created_at' => $account->created_at,
                        'updated_at' => $account->updated_at,
                    ]
                );
            }
        }

        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', \App\Models\TaiKhoan::class)
                ->update(['tokenable_type' => \App\Models\User::class]);

            try {
                DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id BIGINT UNSIGNED');
            } catch (\Throwable $e) {
                // ignore if database driver does not support this rollback change
            }
        }

        Schema::table('TaiKhoan', function (Blueprint $table) {
            if (Schema::hasColumn('TaiKhoan', 'created_at')) {
                $table->dropTimestamps();
            }
            foreach (['remember_token', 'onesignal_player_id', 'email_verified_at', 'email', 'name'] as $column) {
                if (Schema::hasColumn('TaiKhoan', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
