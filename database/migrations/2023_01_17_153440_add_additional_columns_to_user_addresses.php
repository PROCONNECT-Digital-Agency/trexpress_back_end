<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalColumnsToUserAddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('province')->nullable();
            $table->string('apartment')->nullable();
            $table->string('postcode')->nullable();
            $table->string('company_name')->nullable();
            $table->string('city')->nullable();
            $table->string('note')->nullable();
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
            $table->dropColumn('province');
            $table->dropColumn('apartment');
            $table->dropColumn('postcode');
            $table->dropColumn('company_name');
            $table->dropColumn('city');
            $table->dropColumn('note');
        });
    }
}
