<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charges', function(Blueprint $table) {
            $table->increments('id');
            $table->enum('channel', ['alipay', 'wechat'])->comment('渠道名称');
            $table->enum('type', ['qr', 'scan', 'pub', 'wap'])->comment('支付类型');
            $table->string('order_no', 64)->comment('商户订单号');
            $table->string('transaction_no', 64)->nullalbe()->comment('渠道交易单号');
            $table->unsignedInteger('amount')->comment('支付金额');
            $table->enum('currency', ['cny'])->comment('货币类型');
            $table->enum('status', ['open', 'paid', 'refund', 'finish', 'close'])->comment('支付状态');
            $table->timestamp('paid_at')->nullable()->comment('支付完成时间');
            $table->timestamp('expired_at')->nullable()->comment('支付过期时间');
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
        Schema::dropIfExists('charges');
    }
}
