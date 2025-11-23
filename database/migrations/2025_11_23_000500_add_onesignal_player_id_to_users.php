<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'onesignal_player_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('onesignal_player_id', 191)->nullable()->after('id_tk');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'onesignal_player_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onesignal_player_id');
        });
    }
};
