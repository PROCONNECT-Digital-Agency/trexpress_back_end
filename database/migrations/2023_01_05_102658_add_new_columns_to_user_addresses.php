<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToUserAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('passport_secret')->nullable();
            $table->string('number')->nullable();
            $table->integer('user_delivery_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('surname');
            $table->dropColumn('birth_date');
            $table->dropColumn('gender');
            $table->dropColumn('email');
            $table->dropColumn('passport_number');
            $table->dropColumn('passport_secret');
            $table->dropColumn('number');
            $table->dropColumn('user_delivery_id');

        });
    }
}
