<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoticesTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notices')->delete();

        DB::statement('ALTER TABLE notices AUTO_INCREMENT = 1');
        $count = env('SEED_RECORD_COUNT', 30);

        \App\Models\Notice::factory()->count((int)$count)->create();
    }

}
