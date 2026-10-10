<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGetTypeAccessPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('get_type_access_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('access_type_id');
            $table->unsignedBigInteger('format_id');
            $table->timestamps();

            $table->foreign('type_id')
                ->references('id')
                ->on('types')
                ->onDelete('cascade');
            $table->foreign('access_type_id')
                ->references('id')
                ->on('types')
                ->onDelete('cascade');
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
        Schema::dropIfExists('get_type_access_permissions');
    }
}
