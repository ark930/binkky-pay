<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('charge_id');
            $table->string('order_no', 64)->comment('退款单号');
            $table->string('transaction_no', 64)->nullable()->comment('第三方渠道退款单号');
            $table->unsignedInteger('amount')->comment('退款金额，以分为单位');
            $table->enum('currency', ['cny'])->comment('货币类型');
            $table->enum('status', ['succeeded', 'failed'])->comment('退款状态');
            $table->string('description')->nullable()->comment('退款描述');
            $table->timestamp('refunded_at')->nullable()->comment('退款完成时间');
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
        Schema::dropIfExists('refunds');
    }
}
