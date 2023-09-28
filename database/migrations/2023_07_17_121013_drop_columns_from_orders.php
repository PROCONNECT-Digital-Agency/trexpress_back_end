<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsFromOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('usd_price');
            $table->dropColumn('track_code');
            $table->dropColumn('declaration_id');
            $table->dropColumn('product_type_id');
            $table->dropColumn('country_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('usd_price')->nullable();
            $table->integer('track_code')->nullable();
            $table->integer('declaration_id')->nullable();
            $table->integer('product_type_id')->nullable();
            $table->integer('country_id')->nullable();
        });
    }
}
