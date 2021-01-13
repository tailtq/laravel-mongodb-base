<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditObjectsTableToNewFlow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('objects', function (Blueprint $table) {
            $table->unsignedBigInteger('cluster_id')->nullable();
            $table->foreign('cluster_id')->references('id')->on('clusters');

            $table->json('images')->nullable();
            $table->unsignedInteger('frame_from')->nullable();
            $table->unsignedInteger('frame_to')->nullable();
            $table->dateTime('time_from')->nullable();
            $table->dateTime('time_to')->nullable();
            $table->unsignedInteger('confidence_rate')->nullable();

            $table->dropColumn(['image']);
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
            $table->dropForeign(['cluster_id']);
            $table->dropColumn([
                'cluster_id',
                'images',
                'frame_from',
                'frame_to',
                'time_from',
                'time_to',
                'confidence_rate',
            ]);
            $table->string('image')->nullable();
        });
    }
}
