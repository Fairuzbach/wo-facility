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
        Schema::table('work_order_facilities', function (Blueprint $table) {
            //
            Schema::table('work_order_facilities', function (Blueprint $table) {
                $table->unsignedBigInteger('plant_id')->nullable()->after('plant');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_facilities', function (Blueprint $table) {
            //
            $table->dropColumn('plant_id');
        });
    }
};
