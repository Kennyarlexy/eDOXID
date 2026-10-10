<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequiredValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('required_values', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key');
            $table->double('weight');
            $table->string('note')->nullable();
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
        Schema::dropIfExists('required_values');
    }
}
