<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectAppearancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('object_appearances', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->unsignedInteger('object_id');
            $table->unsignedInteger('frame_from');
            $table->unsignedInteger('frame_to')->nullable();
            $table->dateTime('time_from')->nullable();
            $table->dateTime('time_to')->nullable();
            $table->timestamps();

            $table->foreign('object_id')->references('id')->on('objects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('object_appearances', function (Blueprint $table) {
            $table->dropForeign(['object_id']);
        });
        Schema::dropIfExists('object_appearances');
    }
}
