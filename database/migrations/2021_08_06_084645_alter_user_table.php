<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('image')->after('id')->nullable();
            $table->string('last_name')->after('name')->nullable();
            $table->string('mobile_no')->after('email_verified_at')->nullable();
            $table->string('user_type')->after('password')->nullable();
            $table->tinyInteger('status')->after('user_type')->nullable()->default(0);
            $table->longText('api_token')->after('status')->nullable();
            $table->integer('otp')->after('api_token')->nullable();
            $table->string('otp_time')->after('otp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
