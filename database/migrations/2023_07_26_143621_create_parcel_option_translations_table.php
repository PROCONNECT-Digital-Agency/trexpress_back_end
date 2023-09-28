<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParcelOptionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parcel_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parcel_option_id')
                ->constrained('parcel_options')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title', 191);
            $table->unique(['parcel_option_id', 'locale']);
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
        Schema::dropIfExists('parcel_option_translations');
    }
}
