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
            // Tambahkan kolom requester_division (Boleh kosong/nullable agar aman untuk data lama)
            // Kita taruh setelah requester_name agar rapi
            $table->string('requester_division')->nullable()->after('requester_name');
        });
    }

    public function down()
    {
        Schema::table('work_order_facilities', function (Blueprint $table) {
            $table->dropColumn('requester_division');
        });
    }
};
