<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // Menambahkan kolom sub_plant (1 karakter, nullable) setelah plant_id
            $table->string('sub_plant', 1)->nullable()->after('plant_id');

            // Menambahkan composite index untuk performa pencarian filter
            $table->index(['plant_id', 'sub_plant']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // Hapus index terlebih dahulu (praktik yang baik saat rollback)
            $table->dropIndex(['plant_id', 'sub_plant']);

            // Hapus kolom sub_plant
            $table->dropColumn('sub_plant');
        });
    }
};
