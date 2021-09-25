<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrokersFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_brokers', function (Blueprint $table) {
            $table->string('header_image')->nullable();
            $table->string('stamp_image')->nullable();
            $table->string('website')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_brokers', function (Blueprint $table) {
            $table->dropColumn('header_image');
            $table->dropColumn('stamp_image');
            $table->dropColumn('website');
        });
    }
}
