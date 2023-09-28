<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->nullable();
            $table->tinyInteger('input')->default(2);
            $table->string('client_id', 191)->nullable();
            $table->string('secret_id', 191)->nullable();
            $table->boolean('sandbox')->default(0);
            $table->boolean('active')->default(1);
            $table->timestamps();
        });

        Schema::create('payment_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title');
            $table->string('client_title', 191)->nullable();
            $table->string('secret_title', 191)->nullable();

            $table->unique(['payment_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
