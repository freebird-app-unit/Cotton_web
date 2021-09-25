<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTblNegotiationCompleteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_negotiation_complete', function (Blueprint $table) {
            $table->string('lab_report_mime', 191)->nullable()->after('without_gst');
            $table->string('transmit_deal_mime', 191)->nullable()->after('lab_report_mime');
            $table->string('without_gst_mime', 191)->nullable()->after('transmit_deal_mime');
            $table->string('gst_reciept_mime', 191)->nullable()->after('without_gst_mime');
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
            $table->dropColumn(['lab_report_mime', 'transmit_deal_mime', 'without_gst_mime', 'gst_reciept_mime']);
        });
    }
}
