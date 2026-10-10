<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInputByUserToSubmissionPresences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_presences', function (Blueprint $table) {
            $table->string('input_by_user')->default('Tim Pembimbing');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submission_presences', function (Blueprint $table) {
            $table->dropColumn('input_by_user');
        });
    }
}
