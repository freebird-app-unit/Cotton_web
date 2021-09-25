<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblWithoutNegotiationMakeDealTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_without_negotiation_make_deal', function (Blueprint $table) {
            $table->id();
            $table->string('post_notification_id')->nullable();
            $table->string('type')->nullable();
            $table->string('seller_buyer_id')->nullable();
            $table->string('user_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_without_negotiation_make_deal');
    }
}
