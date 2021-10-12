<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBalesCommissionSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_settings', function (Blueprint $table) {
            $table->double('company_commission', 10,2)->after('bunch');
            $table->double('broker_commission', 10,2)->after('company_commission');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_settings', function (Blueprint $table) {
            $table->dropColumn(['company_commission', 'broker_commission']);
        });
    }
}
