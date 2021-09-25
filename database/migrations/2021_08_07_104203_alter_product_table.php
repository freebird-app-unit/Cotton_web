<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_product', function (Blueprint $table) {
            $table->smallInteger('is_delete')->tinyInteger('is_delete')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_product', function (Blueprint $table) {
            $table->tinyInteger('is_delete')->default(0);
        });
    }
}
