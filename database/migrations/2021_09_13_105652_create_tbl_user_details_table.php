<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_user_details', function (Blueprint $table) {
             $table->id();
            $table->string('user_id')->nullable();
            $table->enum('user_type', ['seller', 'buyer','broker'])->nullable();
            $table->string('seller_buyer_type')->nullable();
            $table->string('name_of_contact_person')->nullable();
            $table->string('business_type')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('registration_date')->nullable();
            $table->string('registration_as_msme')->nullable();
            $table->string('turnover_year_one')->nullable();
            $table->string('turnover_date_one')->nullable();
            $table->string('turnover_year_two')->nullable();
            $table->string('turnover_date_two')->nullable();
            $table->string('turnover_year_three')->nullable();
            $table->string('turnover_date_three')->nullable();
            $table->string('oper_in_cotton_trade')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('pan_no_of_buyer')->nullable();
            $table->string('country_id')->nullable();
            $table->string('state_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('station_id')->nullable();
            $table->string('establish_year')->nullable();
            $table->string('company_name')->nullable();
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
        Schema::dropIfExists('tbl_user_details');
    }
}
