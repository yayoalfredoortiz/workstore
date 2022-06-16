<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('leaves')->delete();

        DB::statement('ALTER TABLE leaves AUTO_INCREMENT = 1');

        $count = env('SEED_RECORD_COUNT', 30);
        \App\Models\Leave::factory()->count((int)$count)->create();
    }

}
