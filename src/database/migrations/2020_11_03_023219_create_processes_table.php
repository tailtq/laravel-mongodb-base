<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('camera')->nullable();
            $table->foreign('camera')->references('id')->on('cameras');
            $table->unsignedInteger('user');
            $table->foreign('user')->references('id')->on('users');

            $table->bigInteger('uuid');
            $table->string('name');
            $table->string('url')->nullable(); // old: video_url
            $table->dateTime('started_at')->nullable();
            $table->string('file_root');
            $table->json('config');
            $table->string('thumbnail');
            $table->text('description')->nullable();
            $table->string('status')->default('ready'); // ready, running, paused, stopped
            $table->integer('fps')->default(1);
            $table->integer('total_frames')->nullable();
            $table->string('video_detecting_result')->nullable();
            $table->string('video_result')->nullable();
            $table->dateTime('detecting_start_time')->nullable();
            $table->dateTime('detecting_end_time')->nullable();
            $table->dateTime('rendering_start_time')->nullable();
            $table->dateTime('done_time')->nullable();
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
        Schema::table('processes', function (Blueprint $table) {
            $table->dropForeign(['camera', 'user']);
        });
        Schema::dropIfExists('processes');
    }
}
