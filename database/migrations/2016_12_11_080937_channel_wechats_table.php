<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChannelWechatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_wechats', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('partner_id')->unique()->comment('合作方ID');
            $table->char('appid', 18)->comment('公众账号ID');
            $table->string('mch_id', 32)->comment('商户号');
            $table->char('key', 32)->comment('商户支付密钥');
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
        Schema::dropIfExists('channel_wechats');
    }
}
