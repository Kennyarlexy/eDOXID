<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinalStepSubmissionFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('final_step_submission_files', function (Blueprint $table) {
            $table->id();
            $table->string('final_thesis_file_name')->nullable();
            $table->string('final_thesis_file_url')->nullable();
            $table->enum('final_thesis_file_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->timestamp('final_thesis_file_upload_at')->nullable();
            $table->string('validity_sheet_file_name')->nullable();
            $table->string('validity_sheet_file_url')->nullable();
            $table->enum('validity_sheet_file_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->timestamp('validity_sheet_file_upload_at')->nullable();

            $table->unsignedBigInteger('mahasiswa_id');
            $table->foreign('mahasiswa_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('final_step_submission_files');
    }
}
