<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermConditionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('term_condition_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_condition_id')->constrained('term_conditions')
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('locale')->index();
            $table->unique(['term_condition_id', 'locale']);
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
        Schema::dropIfExists('term_condition_translations');
    }
}
