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
            $table->bigIncrements('id');
            $table->unsignedInteger('app_id');
            $table->enum('channel', ['alipay', 'wechat'])->comment('渠道名称');
            $table->enum('type', ['qr', 'scan', 'pub', 'wap'])->comment('支付类型');
            $table->string('order_no', 64)->comment('商户订单号');
            $table->string('transaction_no', 64)->nullalbe()->comment('渠道交易单号');
            $table->unsignedInteger('amount')->comment('支付金额，以分为单位');
            $table->unsignedInteger('amount_refunded')->default(0)->comment('退款金额，以分为单位');
            $table->unsignedInteger('amount_settled')->comment('清算金额，以分为单位');
//            $table->boolean('refunded')->default(false)->comment('是否有退款，包括全额退款和部分退款');
            $table->string('subject', 64)->comment('支付标题');
            $table->string('body', 128)->comment('支付描述');
            $table->enum('currency', ['cny'])->comment('货币类型');
            $table->enum('status', ['pending', 'succeeded', 'failed', 'closed'])->comment('支付状态');
            $table->string('client_ip', 15)->comment('客户端IP');
            $table->timestamp('paid_at')->nullable()->comment('支付完成时间');
            $table->timestamp('expired_at')->nullable()->comment('支付过期时间');
            $table->string('auth_code')->nullable()->comment('刷卡支付用户支付授权码');
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
