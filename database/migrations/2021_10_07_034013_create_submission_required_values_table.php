<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionRequiredValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_required_values', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->unsignedBigInteger('required_value_id');
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('verificator_id');
            $table->timestamps();

            $table->foreign('required_value_id')
                ->references('id')
                ->on('required_values')
                ->onDelete('cascade');
            $table->foreign('submission_id')
                ->references('id')
                ->on('submissions')
                ->onDelete('cascade');
            $table->foreign('verificator_id')
                ->references('id')
                ->on('verificators')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_required_values');
    }
}
