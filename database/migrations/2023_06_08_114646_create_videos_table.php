<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name');
            $table->string('banner');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('video_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_video_id')->constrained('videos')->cascadeOnDelete();
            $table->string('locale');
            $table->string('title');
            $table->text('description');
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
        Schema::dropIfExists('video_translations');
        Schema::dropIfExists('videos');
    }
}