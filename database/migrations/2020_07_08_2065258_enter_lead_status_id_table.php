<?php

use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Database\Migrations\Migration;

class EnterLeadStatusIdTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $status = LeadStatus::where('default', '1')->first();

        if(is_null($status)){
            $status = LeadStatus::orderBy('id', 'asc')->first();

            if(!is_null($status)){
                $status->default = '1';
                $status->save();
            }
        }

        if(!is_null($status)){
            $leads = Lead::whereNull('status_id')->select('id', 'status_id')->get();

            foreach($leads as $lead){
                $lead->status_id = $status->id;
                $lead->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }

}
