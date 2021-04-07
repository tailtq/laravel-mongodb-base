<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('identities', function (Blueprint $table) {
            $table->integerIncrements('id');

            $table->unsignedInteger('process')->nullable();
            $table->foreign('process')->references('id')->on('processes');

            $table->string('name');
            $table->string('card_number', 20);
            $table->string('type', 20);
            $table->string('status')->default('tracking'); // tracking, untracking
            $table->text('info')->nullable();
            $table->json('matching_face_ids')->default('[]');
            $table->json('clustering_face_ids')->default('[]');
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
        Schema::table('identities', function (Blueprint $table) {
            $table->dropForeign(['process']);
        });
        Schema::dropIfExists('identities');
    }
}
