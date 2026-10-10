<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillGroupLecturersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skill_group_lecturers', function (Blueprint $table) {
            $table->id();
            $table->string('position');
            $table->unsignedBigInteger('lecturer_id');
            $table->unsignedBigInteger('skill_group_id');
            $table->timestamps();

            $table->foreign('lecturer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('skill_group_id')->references('id')->on('skill_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skill_group_lecturers');
    }
}
