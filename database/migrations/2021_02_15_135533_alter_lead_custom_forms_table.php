<?php

use App\Models\LeadCustomForm;
use App\Models\TicketCustomForm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AlterLeadCustomFormsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        LeadCustomForm::create([
            'field_display_name' => 'Message',
            'field_name' => 'message',
            'field_order' => 7,
        ]);

        Schema::table('organisation_settings', function (Blueprint $table) {
            $table->boolean('ticket_form_google_captcha')->default(0);
            $table->boolean('lead_form_google_captcha')->default(0);
        });

        TicketCustomForm::where('field_name', 'ticket_description')->update(['field_name' => 'message', 'field_display_name' => 'Message']);

        Schema::table('lead_custom_forms', function (Blueprint $table) {
            $table->boolean('required')->default(0);
        });
        Schema::table('ticket_custom_forms', function (Blueprint $table) {
            $table->boolean('required')->default(0);
        });

        $leadForm = LeadCustomForm::all();

        foreach($leadForm as $form)
        {
            if($form->field_name == 'name' || $form->field_name == 'email')
            {
                $form->required = 1;
                $form->save();
            }
        }

        $ticketForm = TicketCustomForm::all();

        foreach($ticketForm as $ticket)
        {
            if($ticket->field_name == 'name' || $ticket->field_name == 'email' || $ticket->field_name == 'ticket_subject' || $ticket->field_name == 'message')
            {
                $ticket->required = 1;
                $ticket->save();
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
        Schema::table('organisation_settings', function (Blueprint $table) {
            $table->dropColumn('ticket_form_google_captcha');
            $table->dropColumn('lead_form_google_captcha');
        });
    }

}
