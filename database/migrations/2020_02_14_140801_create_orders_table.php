<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('custom_id')->unsigned();
            $table->bigInteger('order_sn')->unique();
            $table->string('mobile')->unique()->nullable();
            $table->decimal('order_amount', 10, 2)->default(0);
            $table->tinyInteger('order_status')->default(1); // 0. 已取消 1.等待批价, 2.等待用户确认, 3.用户已经确认.商家准备中, 4.准备完毕 , 5.已完成
            $table->integer('pay_screenshot_id')->nullable(); // 截图凭据
            $table->bigInteger('store_id')->unsigned();
            $table->string('remark');
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
        Schema::dropIfExists('orders');
    }
}
