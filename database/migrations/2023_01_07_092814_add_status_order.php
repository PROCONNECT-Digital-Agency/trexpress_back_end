<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->float('total_delivery_fee')->nullable();
            $table->integer('user_address_id')->nullable();
            $table->integer('track_code')->nullable();
            $table->integer('declaration_id')->nullable();
            $table->float('tax')->nullable();
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
            $table->dropColumn('status');
            $table->dropColumn('total_delivery_fee');
            $table->dropColumn('user_address_id');
            $table->dropColumn('track_code');
            $table->dropColumn('declaration_id');
            $table->dropColumn('tax');
        });
    }
}
