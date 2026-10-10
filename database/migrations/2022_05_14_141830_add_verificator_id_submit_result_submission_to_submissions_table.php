<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificatorIdSubmitResultSubmissionToSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('verificator_id_who_submit_result')->nullable();
            $table->dateTime('updated_submit_result_at')->nullable();
            $table->foreign('verificator_id_who_submit_result')->references('id')->on('verificators')->onDelete('set null');
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
            $table->dropForeign(['verificator_id_who_submit_result']);
            $table->dropColumn('verificator_id_who_submit_result');
            $table->dropColumn('updated_submit_result_at');
        });
    }
}
