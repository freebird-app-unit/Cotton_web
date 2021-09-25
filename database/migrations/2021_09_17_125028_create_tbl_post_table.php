<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_post', function (Blueprint $table) {
             $table->id();
            $table->enum('status', ['active', 'complete','cancel'])->nullable();
            $table->string('seller_buyer_id')->nullable();
            $table->enum('user_type', ['seller', 'buyer'])->nullable();
            $table->string('product_id')->nullable();
            $table->string('no_of_bales')->nullable();
            $table->string('price')->nullable();
            $table->string('address')->nullable();
            $table->string('d_e')->nullable();
            $table->string('buy_for')->nullable();
            $table->string('spinning_meal_name')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->string('sold_bales')->default(0);
            $table->string('remain_bales')->default(0);
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
        Schema::dropIfExists('tbl_post');
    }
}
