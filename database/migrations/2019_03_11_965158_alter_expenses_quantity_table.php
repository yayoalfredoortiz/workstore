<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterExpensesQuantityTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `estimate_items` CHANGE `quantity` `quantity` DOUBLE(16,2) NOT NULL');
        DB::statement('ALTER TABLE `invoice_items` CHANGE `quantity` `quantity` DOUBLE(16,2) NOT NULL');
        DB::statement('ALTER TABLE `proposal_items` CHANGE `quantity` `quantity` DOUBLE(16,2) NOT NULL');
        DB::statement('ALTER TABLE `expenses` CHANGE `price` `price` DOUBLE(16,2) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

}
