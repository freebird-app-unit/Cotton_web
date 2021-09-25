<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblNegotiationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_negotiation', function (Blueprint $table) {
            $table->id();
            $table->integer('buyer_id');
            $table->integer('seller_id');
            $table->enum('negotiation_by', ['seller', 'buyer'])->nullable();
            $table->integer('post_notification_id');
            $table->enum('negotiation_type', ['post', 'notification'])->nullable();
            $table->double('price', 10,2)->nullable();
            $table->integer('bales')->nullable();
            $table->enum('status', ['complete','incomplete'])->nullable();
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
        Schema::dropIfExists('tbl_negotiation');
    }
}
