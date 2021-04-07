<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClusterElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cluster_elements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cluster');
            $table->foreign('cluster')->references('id')->on('clusters');
            $table->unsignedInteger('object');
            $table->foreign('object')->references('id')->on('objects');
            $table->unsignedInteger('ref_object')->nullable();
            $table->foreign('ref_object')->references('id')->on('objects');
            $table->string('type');
            $table->float('distance');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cluster_elements', function (Blueprint $table) {
            $table->dropForeign(['cluster', 'object', 'ref_object']);
        });
        Schema::dropIfExists('cluster_elements');
    }
}
