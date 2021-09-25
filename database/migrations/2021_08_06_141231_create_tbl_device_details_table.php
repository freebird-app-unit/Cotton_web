<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblDeviceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_device_details', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->enum('user_type', ['seller', 'buyer','broker'])->nullable();
            $table->string('api_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('device_token')->nullable();
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
        Schema::dropIfExists('tbl_device_details');
    }
}
