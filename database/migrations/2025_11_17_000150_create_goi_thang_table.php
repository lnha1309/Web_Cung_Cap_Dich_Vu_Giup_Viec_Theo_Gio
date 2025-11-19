<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('GoiThang', function (Blueprint $table) {
            $table->string('ID_Goi', 50)->primary();
            $table->string('TenGoi', 255)->nullable();
            $table->integer('SoNgay')->nullable();
            $table->decimal('PhanTramGiam', 5, 2)->nullable();
            $table->text('Mota')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('GoiThang');
    }
};

