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
            $table->unsignedInteger('identity_id')->nullable();
            $table->unsignedInteger('process_id');
            $table->string('mongo_id');
            $table->unsignedInteger('track_id');
            $table->timestamps();

            $table->foreign('identity_id')->references('id')->on('identities');
            $table->foreign('process_id')->references('id')->on('processes');
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
            $table->dropForeign(['identity_id']);
        });
        Schema::dropIfExists('objects');
    }
}
