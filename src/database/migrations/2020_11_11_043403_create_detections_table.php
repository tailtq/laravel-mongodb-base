<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detections', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('object');
            $table->foreign('object')->references('id')->on('objects');

            $table->unsignedInteger('frame_index');
            $table->json('head_bbox')->nullable();
            $table->unsignedSmallInteger('head_confidence')->nullable();
            $table->json('face_bbox')->nullable();
            $table->unsignedSmallInteger('face_confidence')->nullable();
            $table->json('body_bbox')->nullable();
            $table->unsignedSmallInteger('body_confidence')->nullable();
            $table->json('head_pose')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('detections', function (Blueprint $table) {
            $table->dropForeign(['object']);
        });
        Schema::dropIfExists('detections');
    }
}
