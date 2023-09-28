<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToOrderCoupons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_coupons', function (Blueprint $table) {
            $table->dropForeign('order_coupons_order_detail_id_foreign');
            $table->dropColumn('order_detail_id');
            $table->foreignId('order_id')->constrained()
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_coupons', function (Blueprint $table) {
            $table->foreignId('order_detail_id')->constrained()
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->dropForeign('order_coupons_order_id_foreign');
            $table->dropColumn('order_id');
        });
    }
}
