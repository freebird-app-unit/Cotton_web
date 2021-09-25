<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTblNegotiationCompleteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_negotiation_complete', function (Blueprint $table) {
            $table->string('lab_report', 191)->nullable()->after('lab');
            $table->string('transmit_deal', 191)->nullable()->after('lab_report');
            $table->string('without_gst', 191)->nullable()->after('transmit_deal');
            $table->string('gst_reciept', 191)->nullable()->after('without_gst');
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
			$table->dropColumn(['lab_report', 'transmit_deal', 'without_gst', 'gst_reciept']);
        });
    }
}
