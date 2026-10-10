<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'processed', 'approved', 'repeated', 'rejected'])->default('pending');
            $table->dateTime('schedule')->nullable();
            $table->string('schedule_id')->nullable();
            $table->dateTime('schedule_request')->nullable();
            $table->string('room')->nullable();
            $table->string('record_url')->nullable();
            $table->string('grade')->nullable();
            $table->string('score')->nullable();
            $table->string('grade_description')->nullable();
            $table->enum('grade_status', ['repeated', 'approved', 'rejected'])->nullable();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('skill_group_id')->nullable();
            $table->unsignedBigInteger('format_id');
            $table->string('document_format')->nullable();
            $table->string('academic_year');
            $table->string('amount_sks_now')->nullable();
            $table->string('ipk_now')->nullable();
            $table->string('semester');
            $table->boolean('is_read_by_tata_usaha')->default(false);
            $table->boolean('is_ready_to_be_processed')->default(false);
            $table->string('final_document_url')->nullable();
            $table->string('final_document_name')->nullable();
            $table->string('final_document_created_at')->nullable();
            $table->unsignedBigInteger('spota_submission_id');
            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('type_id')
                ->references('id')
                ->on('types')
                ->onDelete('cascade');
            $table->foreign('skill_group_id')
                ->references('id')
                ->on('skill_groups')
                ->onDelete('set null');
            $table->foreign('format_id')
                ->references('id')
                ->on('formats')
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
        Schema::dropIfExists('submissions');
    }
}
