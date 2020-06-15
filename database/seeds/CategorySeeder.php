<?php

use Illuminate\Database\Seeder;
use App\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = [
            [
                'category'=>'Bottoms',
            ],
            [
                'category'=>'Blazer-Jacket',
            ],
            [
                'category'=>'Outers-Coat',
            ],
            [
                'category'=>'Tops',
            ],
            [
                'category'=>'Dresses',
            ],
            [
                'category'=>'Jumpsuits',
            ],
        ];

        foreach ($category as $key => $value) {
            Category::create($value);
        }
    }
}
