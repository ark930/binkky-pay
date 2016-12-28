<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelUnionPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_union_pays', function(Blueprint $table) {
            $table->increments('id');
            $table->char('mer_id', 15)->comment('银联商户号');
            $table->text('cert')->comment('银联用户证书');
            $table->string('cert_password', 64)->nullable()->comment('银联用户证书密码');
            $table->string('cert_id', 64)->comment('银联用户证书序列号');
            $table->text('cert_private_key')->comment('银联用户证书私钥');
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
        Schema::dropIfExists('channel_union_pays');
    }
}
