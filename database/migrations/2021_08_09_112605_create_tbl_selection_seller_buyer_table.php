<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblSelectionSellerBuyerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_selection_seller_buyer', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['seller', 'buyer'])->nullable();
            $table->string('seller_buyer_id')->nullable();
            $table->string('notification_id')->nullable();
            $table->string('broker_type')->nullable();
            $table->string('broker_id')->nullable();
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
        Schema::dropIfExists('tbl_selection_seller_buyer');
    }
}
