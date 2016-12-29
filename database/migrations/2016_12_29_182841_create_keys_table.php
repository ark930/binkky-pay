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
            $table->char('key', 20)->comment('接口密钥');
            $table->unsignedInteger('partner_id')->comment('合作方ID');
            $table->timestamp('created_at');
            $table->softDeletes();
            $table->primary(['key', 'partner_id']);
            $table->unique('key');
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
