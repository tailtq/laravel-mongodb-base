<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('process');
            $table->foreign('process')->references('id')->on('processes');

            $table->unsignedInteger('identity')->nullable();
            $table->foreign('identity')->references('id')->on('identities');

            $table->bigInteger('uuid');
            $table->unsignedInteger('track_id');
            $table->json('face_ids')->default('[]');
            $table->json('body_ids')->default('[]');
            $table->json('avatars')->default('[]');
            $table->unsignedInteger('from_frame')->nullable();
            $table->unsignedInteger('to_frame')->nullable();
            $table->dateTime('from_time')->nullable();
            $table->dateTime('to_time')->nullable();

            $table->boolean('have_new_body')->default(false);
            $table->boolean('have_new_face')->default(false);

            $table->unsignedInteger('confidence_rate')->nullable();
            $table->float('similarity_distance')->nullable();

            $table->string('video_result')->nullable();
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
        Schema::table('objects', function (Blueprint $table) {
            $table->dropForeign(['process', 'identity']);
        });
        Schema::dropIfExists('objects');
    }
}
