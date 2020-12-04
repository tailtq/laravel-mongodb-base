<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeasuringTimesToProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dateTime('detecting_start_time')->nullable();
            $table->dateTime('grouping_start_time')->nullable();
            $table->dateTime('rendering_start_time')->nullable();
            $table->dateTime('done_time')->nullable();
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
            $table->dropColumn([
                'detecting_start_time',
                'grouping_start_time',
                'rendering_start_time',
                'done_time',
            ]);
        });
    }
}
