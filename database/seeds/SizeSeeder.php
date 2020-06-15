<?php

use Illuminate\Database\Seeder;
use App\Size;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $size = [
            [
                'size'=>'All Size',
            ],
            [
                'size'=>'XS',
            ],
            [
                'size'=>'S',
            ],
            [
                'size'=>'M',
            ],
            [
                'size'=>'L',
            ],
            [
                'size'=>'XL',
            ],
            [
                'size'=>'XXL',
            ],
        ];

        foreach ($size as $key => $value) {
            Size::create($value);
        }
    }
}
