<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PhuThu', function (Blueprint $table) {
            $table->string('ID_PT', 50)->primary();
            $table->string('Ten_PT', 255)->nullable();
            $table->decimal('GiaCuoc', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PhuThu');
    }
};

