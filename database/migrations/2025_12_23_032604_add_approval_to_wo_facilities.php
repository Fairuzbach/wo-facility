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
            //status internal divisi
            $table->string('internal_status')->default('waiting_spv')->after('status');

            //data pelapor
            $table->string('requester_nik')->nullable();
            $table->string('requester_divison')->nullable();

            //log siapa yang approve
            $table->string('approved_by_nik')->nullable();
            $table->string('approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wo_facilities', function (Blueprint $table) {
            //
        });
    }
};
