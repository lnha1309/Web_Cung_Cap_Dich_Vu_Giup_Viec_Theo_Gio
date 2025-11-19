<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cho phep ID_KH duoc null de luu dia chi chi gan voi DonDat
        DB::statement('ALTER TABLE DiaChi MODIFY ID_KH VARCHAR(50) NULL');
    }

    public function down(): void
    {
        // Quay lai khong cho null (neu can)
        DB::statement('ALTER TABLE DiaChi MODIFY ID_KH VARCHAR(50) NOT NULL');
    }
};

