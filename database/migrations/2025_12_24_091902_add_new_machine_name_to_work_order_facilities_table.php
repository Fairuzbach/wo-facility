<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('work_order_facilities', function (Blueprint $table) {
            // Menambahkan kolom teks yang boleh kosong
            $table->string('new_machine_name')->nullable()->after('machine_id');
        });
    }

    public function down()
    {
        Schema::table('work_order_facilities', function (Blueprint $table) {
            $table->dropColumn('new_machine_name');
        });
    }
};
