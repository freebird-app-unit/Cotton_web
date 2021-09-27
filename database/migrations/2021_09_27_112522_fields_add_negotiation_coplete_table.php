<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FieldsAddNegotiationCopleteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_negotiation_complete', function (Blueprint $table) {
            $table->integer('broker_id')->nullable()->after('seller_id');
            $table->text('notes')->nullable()->after('lab_report_status');
            $table->string('header')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_negotiation_complete', function (Blueprint $table) {
            $table->dropColumn(['broker_id','notes','header']);
        });
    }
}
