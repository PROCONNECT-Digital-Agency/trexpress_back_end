<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')->constrained()
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained('stocks');
            $table->double('origin_price')->default(0);
            $table->double('total_price')->default(0);
            $table->double('tax', 20)->default(0);
            $table->double('discount', 20)->default(0);
            $table->integer('quantity')->default(0);

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
        Schema::dropIfExists('order_products');
    }
}
