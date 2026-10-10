<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsNeedToChangeTrialStatusToVerificatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('verificators', function (Blueprint $table) {
            $table->boolean('is_need_to_change_trial_status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verificators', function (Blueprint $table) {
            $table->dropColumn('is_need_to_change_trial_status');
        });
    }
}
