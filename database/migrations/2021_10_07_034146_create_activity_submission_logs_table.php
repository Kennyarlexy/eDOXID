<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitySubmissionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_submission_logs', function (Blueprint $table) {
            $table->id();
            $table->text('details');
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_submission_logs');
    }
}
