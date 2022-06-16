<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoginBackgroundOrganisationSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organisation_settings', function(Blueprint $table){
            $table->string('login_background')->nullable()->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organisation_settings', function(Blueprint $table){
            $table->dropColumn('login_background');
        });
    }

}
