<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keys', function(Blueprint $table) {
            $table->increments('partner_id')->comment('合作方ID, 对应线下门店或者线上APP');
            $table->char('app_id', 18)->unique()->commnet('接口应用ID');
            $table->char('app_key', 32)->unique()->comment('接口密钥');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keys');
    }
}
