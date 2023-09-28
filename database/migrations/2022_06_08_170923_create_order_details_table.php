<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id()->from(500);
            $table->foreignId('order_id')->constrained('orders')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->double('price', 20);
            $table->double('tax')->default(1);
            $table->double('commission_fee')->nullable();
            $table->string('status')->default('new');
            $table->foreignId('delivery_address_id')->nullable()->index()
                ->constrained('user_addresses');
            $table->foreignId('delivery_type_id')->nullable()->constrained('deliveries');
            $table->double('delivery_fee', 20)->default(0);
            $table->foreignId('deliveryman')->nullable()->index();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_time')->nullable();
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
        Schema::dropIfExists('order_details');
    }
}
