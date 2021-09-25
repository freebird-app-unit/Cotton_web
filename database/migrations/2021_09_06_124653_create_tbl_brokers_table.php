<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblBrokersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_brokers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('mobile_number_2')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('code')->nullable();
            $table->tinyInteger('is_approve')->default('0');
            $table->tinyInteger('is_active')->default('1');
            $table->tinyInteger('is_delete')->default('1');
            $table->string('otp')->nullable();
            $table->string('otp_time')->nullable();
            $table->tinyInteger('is_otp_verify')->default('0');
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
        Schema::dropIfExists('tbl_brokers');
    }
}
