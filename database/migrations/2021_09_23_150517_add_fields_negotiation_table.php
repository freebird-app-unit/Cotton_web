<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsNegotiationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_negotiation', function (Blueprint $table) {
            $table->integer('broker_id')->nullable()->after('lab');
            $table->integer('header')->nullable()->after('broker_id');            
            $table->text('notes')->nullable()->after('header');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_negotiation', function (Blueprint $table) {
            $table->dropColumn(['broker_id', 'header', 'notes']);
        });
    }
}
