<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('document_format')->nullable();
            $table->text('document_format_name')->nullable();
            $table->text('document_format_size')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_pembimbing_required')->default(false);
            $table->boolean('is_penguji_required')->default(false);
            $table->boolean('is_tata_usaha_required')->default(false);
            $table->boolean('is_skill_group_required')->default(false);
            $table->boolean('is_need_trial_schedule')->default(false);
            $table->boolean('is_all_lecturers_can_consultations')->default(false);
            $table->boolean('if_approved_next_submission_using_previous_verificators')->default(false);
            $table->unsignedBigInteger('type_id');
            $table->timestamps();

            $table->foreign('type_id')
                ->references('id')
                ->on('types')
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
        Schema::dropIfExists('formats');
    }
}
