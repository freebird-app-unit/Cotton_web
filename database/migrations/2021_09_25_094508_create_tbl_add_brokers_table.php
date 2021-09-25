<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblAddBrokersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_add_brokers', function (Blueprint $table) {
            $table->id();
            $table->string('buyer_id')->nullable();
            $table->enum('user_type', ['seller', 'buyer'])->nullable();
            $table->string('broker_id')->nullable();
            $table->string('broker_type')->nullable();
            $table->string('otp')->nullable();
            $table->string('otp_time')->nullable();
            $table->string('is_verify')->default(0);
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
        Schema::dropIfExists('tbl_add_brokers');
    }
}
