<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ChiTietPhuThu', function (Blueprint $table) {
            $table->string('ID_PT', 50);
            $table->string('ID_DD', 50);
            $table->text('Ghichu')->nullable();

            $table->primary(['ID_PT', 'ID_DD']);

            $table->foreign('ID_DD')
                ->references('ID_DD')
                ->on('DonDat');

            $table->foreign('ID_PT')
                ->references('ID_PT')
                ->on('PhuThu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ChiTietPhuThu');
    }
};

