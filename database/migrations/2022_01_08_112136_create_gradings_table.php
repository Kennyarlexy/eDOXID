<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gradings', function (Blueprint $table) {
            $table->id();
            $table->double('maksimal_score');
            $table->double('minimal_score');
            $table->string('grade');
            $table->string('description')->nullable();
            $table->enum('submission_status', ['approved', 'rejected', 'repeated'])->default('repeated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gradings');
    }
}
