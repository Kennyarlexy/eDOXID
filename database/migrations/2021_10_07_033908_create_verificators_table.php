<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verificators', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Pembimbing', 'Penguji', 'Pembimbing Akademik']);
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->enum('final_status', ['pending', 'approved', 'rejected']);
            $table->longText('note')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('submission_id');
            $table->boolean('is_read_by_dosen')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('verificators');
    }
}
