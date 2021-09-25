<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblNegotiationCompleteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_negotiation_complete', function (Blueprint $table) {
            $table->id();
            $table->string('negotiation_id')->nullable();
            $table->string('buyer_id')->nullable();
            $table->string('seller_id')->nullable();
            $table->enum('negotiation_by', ['seller', 'buyer'])->nullable();
            $table->string('post_notification_id')->nullable();
            $table->enum('negotiation_type', ['post', 'notification'])->nullable();
            $table->string('price')->nullable();
            $table->string('no_of_bales')->nullable();
            $table->string('payment_condition')->nullable();
            $table->string('transmit_condition')->nullable();
            $table->string('lab')->nullable();
            $table->tinyInteger('is_deal')->default(0);
            $table->string('done_by')->nullable();
            $table->string('status')->default(0);
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
        Schema::dropIfExists('tbl_negotiation_complete');
    }
}
