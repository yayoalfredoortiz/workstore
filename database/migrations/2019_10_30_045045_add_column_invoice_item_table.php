<?php

use App\Models\InvoiceItems;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnInvoiceItemTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('taxes')->nullable()->default(null)->after('amount');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign('invoice_items_tax_id_foreign');
            $table->dropColumn('tax_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('taxes');
            $table->integer('tax_id')->unsigned()->nullable()->after('amount');
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('cascade')->onUpdate('cascade');
        });
    }

}
