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
            $table->string('requester_email')->nullable()->after('requester_name');
        });
    }

    public function down()
    {
        Schema::table('work_order_facilities', function (Blueprint $table) {
            $table->dropColumn('requester_email');
        });
    }
};
