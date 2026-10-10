<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formats', function (Blueprint $table) {
            $table->boolean('is_pembimbing_need_to_change_trial_status')->default(false);
            $table->boolean('is_penguji_need_to_change_trial_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formats', function (Blueprint $table) {
            $table->boolean('is_pembimbing_need_to_change_trial_status')->default(false);
            $table->boolean('is_penguji_need_to_change_trial_status')->default(false);
        });
    }
}
