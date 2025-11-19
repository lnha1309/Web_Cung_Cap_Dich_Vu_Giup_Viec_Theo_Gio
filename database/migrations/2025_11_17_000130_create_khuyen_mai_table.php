<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('KhuyenMai', function (Blueprint $table) {
            $table->string('ID_KM', 50)->primary();
            $table->string('Ten_KM', 255)->nullable();
            $table->text('MoTa')->nullable();
            $table->decimal('PhanTramGiam', 5, 2)->nullable();
            $table->decimal('GiamToiDa', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('KhuyenMai');
    }
};
