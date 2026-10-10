<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequiredFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('required_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('note')->nullable();
            $table->enum('type_file', ['all', 'image', 'document_word', 'pdf']);
            $table->boolean('is_verificator_can_edit')->default(false);
            $table->boolean('is_verificator_can_view')->default(false);
            $table->unsignedBigInteger('format_id');
            $table->timestamps();

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
        Schema::dropIfExists('required_files');
    }
}
