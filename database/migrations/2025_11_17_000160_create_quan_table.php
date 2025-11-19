<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Quan', function (Blueprint $table) {
            $table->string('ID_Quan', 50)->primary();
            $table->string('TenQuan', 255);
            $table->decimal('ViDo', 10, 7)->nullable();
            $table->decimal('KinhDo', 10, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Quan');
    }
};

