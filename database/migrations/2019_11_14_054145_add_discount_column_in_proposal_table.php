<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscountColumnInProposalTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->enum('discount_type', ['percent', 'fixed'])->after('currency_id');
            $table->double('discount')->after('discount_type');
            $table->boolean('invoice_convert')->default(0)->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['discount_type']);
            $table->dropColumn(['discount']);
            $table->dropColumn(['invoice_convert']);
        });
    }

}
