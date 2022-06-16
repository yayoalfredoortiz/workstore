<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCronJobMessageHideTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisation_settings', function (Blueprint $table) {
            $table->boolean('hide_cron_message')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisation_settings', function (Blueprint $table) {
            $table->dropColumn(['hide_cron_message']);
        });
    }

}
