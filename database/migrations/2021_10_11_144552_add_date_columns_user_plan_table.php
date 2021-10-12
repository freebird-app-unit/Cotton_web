<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateColumnsUserPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_users_plan', function (Blueprint $table) {
            $table->date('purchase_date')->after('status')->nullable();
            $table->date('expiry_date')->after('purchase_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_users_plan', function (Blueprint $table) {
            $table->dropColumn(['purchase_date', 'expiry_date']);
        });
    }
}
