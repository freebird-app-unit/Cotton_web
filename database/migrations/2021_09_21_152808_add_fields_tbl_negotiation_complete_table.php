<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsTblNegotiationCompleteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_negotiation_complete', function (Blueprint $table) {
            $table->string('lab_report_upload_by', 50)->nullable()->after('gst_reciept_mime');
            $table->string('transmit_deal_upload_by', 50)->nullable()->after('lab_report_upload_by');
            $table->string('without_gst_upload_by', 50)->nullable()->after('transmit_deal_upload_by');
            $table->string('gst_reciept_upload_by', 50)->nullable()->after('without_gst_upload_by');
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
            $table->dropColumn(['lab_report_upload_by', 'transmit_deal_upload_by', 'without_gst_upload_by', 'gst_reciept_upload_by']);
        });
    }
}
