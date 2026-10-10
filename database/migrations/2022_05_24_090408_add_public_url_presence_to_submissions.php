<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicUrlPresenceToSubmissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dateTime('presence_public_url_open_datetime')->nullable();
            $table->dateTime('presence_public_url_close_datetime')->nullable();
            $table->enum('presence_public_url_status', ['active', 'inactive'])->default('inactive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('presence_public_url_open_datetime');
            $table->dropColumn('presence_public_url_close_datetime');
            $table->dropColumn('presence_public_url_status');
        });
    }
}
