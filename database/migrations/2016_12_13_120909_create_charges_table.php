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
            $table->unsignedInteger('partner_id')->comment('合作方ID');
            $table->enum('channel', ['alipay', 'wechat', 'union_pay'])->comment('渠道名称');
            $table->enum('type', ['qr', 'scan', 'pub', 'wap', 'app'])->comment('支付类型');
            $table->string('trade_no', 64)->collation('utf8_bin')->comment('商户交易单号');
            $table->string('tn', 64)->nullable()->comment('渠道交易单号, Transaction Number');
            $table->unsignedInteger('amount')->comment('支付金额，以分为单位');
            $table->unsignedInteger('amount_refunded')->default(0)->comment('退款金额，以分为单位');
            $table->unsignedInteger('amount_settled')->comment('清算金额，以分为单位');
//            $table->boolean('refunded')->default(false)->comment('是否有退款，包括全额退款和部分退款');
            $table->string('title', 64)->comment('支付标题');
            $table->string('desc', 128)->comment('支付描述');
            $table->enum('currency', ['cny'])->comment('货币类型');
            $table->string('client_ip', 15)->comment('客户端IP');
            $table->string('notify_url')->comment('商户异步通知地址');
            $table->string('openid')->comment('用户标识');
//            $table->string('auth_code')->nullable()->comment('刷卡支付用户支付授权码');
            $table->enum('status', ['pending', 'succeeded', 'failed', 'closed'])->comment('支付状态');
            $table->timestamp('paid_at')->nullable()->comment('支付完成时间');
            $table->timestamp('expired_at')->nullable()->comment('支付过期时间');
            $table->timestamps();

            $table->index('partner_id');
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
