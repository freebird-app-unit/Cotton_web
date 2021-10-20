<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNegotiationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_negotiation', function (Blueprint $table) {
            $table->integer('broker_id')->nullable()->after('seller_id');
            $table->double('prev_price', 10,2)->nullable()->after('bales');
            $table->integer('prev_bales')->nullable()->after('prev_price');
            $table->text('notes')->nullable()->after('prev_bales');
            $table->string('header')->nullable()->after('notes');
            $table->integer('payment_condition')->nullable()->after('header');
            $table->integer('transmit_condition')->nullable()->after('payment_condition');
            $table->integer('lab')->nullable()->after('transmit_condition');
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
            $table->dropColumn(['broker_id','prev_price','prev_bales','notes','header','payment_condition','transmit_condition','lab']);
        });
    }
}
