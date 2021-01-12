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

            $table->json('avatars')->nullable();
            $table->unsignedInteger('frame_from')->nullable();
            $table->unsignedInteger('frame_to')->nullable();
            $table->dateTime('time_from')->nullable();
            $table->dateTime('time_to')->nullable();
            $table->unsignedInteger('accuracy_rate')->nullable();
            $table->unsignedFloat('confidence_score')->nullable();

            $table->dropColumn(['avatar']);
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
                'avatars',
                'frame_from',
                'frame_to',
                'time_from',
                'time_to',
                'accuracy_rate',
                'confidence_score',
            ]);
            $table->string('avatar')->nullable();
        });
    }
}
