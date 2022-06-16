<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = new \App\Models\ProjectCategory();
        $category->category_name = 'Laravel';
        $category->save();

        $category = new \App\Models\ProjectCategory();
        $category->category_name = 'Yii';
        $category->save();

        $category = new \App\Models\ProjectCategory();
        $category->category_name = 'Zend';
        $category->save();

        $category = new \App\Models\ProjectCategory();
        $category->category_name = 'CakePhp';
        $category->save();

        $category = new \App\Models\ProjectCategory();
        $category->category_name = 'Codeigniter';
        $category->save();
    }

}
