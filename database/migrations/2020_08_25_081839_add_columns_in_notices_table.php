<?php

use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInNoticesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notices', function (Blueprint $table) {
            if (!Schema::hasColumn('notices', 'department_id')){
                $table->unsignedInteger('department_id')->nullable()->default(null);
                $table->foreign('department_id')->references('id')->on('teams')->onDelete('cascade')->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notices', function (Blueprint $table) {
            if (Schema::hasColumn('notices', 'department_id')){
                $table->dropForeign(['department_id']);
                $table->dropColumn(['department_id']);
            }
        });
    }

}
