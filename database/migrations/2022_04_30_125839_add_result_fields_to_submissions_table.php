<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResultFieldsToSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->enum('result_status', ['approved', 'approved_with_fixed', 'rejected', 'repeated', 'pending'])->default('pending');
            $table->dateTime('recomendation_repeated_schedule')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('result_status');
            $table->dropColumn('recomendation_repeated_schedule');
        });
    }
}
