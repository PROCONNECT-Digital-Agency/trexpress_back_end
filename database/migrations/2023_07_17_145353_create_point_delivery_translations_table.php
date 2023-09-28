<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointDeliveryTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_delivery_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('point_delivery_id')->constrained('point_deliveries')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->string('locale')->index();
            $table->unique(['point_delivery_id', 'locale']);
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
        Schema::dropIfExists('point_delivery_translations');
    }
}
