<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionPresencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_presences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unique_id');
            $table->string('keterangan');
            $table->string('signature_image')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('submission_id');
            $table->foreign('submission_id')->references('id')->on('submissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_presences');
    }
}
