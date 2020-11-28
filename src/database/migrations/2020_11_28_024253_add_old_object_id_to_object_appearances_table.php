<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOldObjectIdToObjectAppearancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('object_appearances', function (Blueprint $table) {
            $table->unsignedInteger('old_object_id')->nullable();
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
            $table->dropColumn(['old_object_id']);
        });
    }
}
