<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlipaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_alipays', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('merchant_id');
            $table->char('appid', 16)->comment('支付宝APPID');
            $table->text('public_key')->comment('应用公钥, 由商户生成');
            $table->text('private_key')->comment('应用私钥, 由商户生成');
            $table->text('alipay_public_key')->comment('支付宝公钥');
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
        Schema::dropIfExists('channel_alipays');
    }
}
