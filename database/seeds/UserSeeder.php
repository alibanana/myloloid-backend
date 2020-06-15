<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            [
                'name'=>'Admin',
                'email'=>'admin@test.com',
                'phone'=>'08121231234',
                'is_admin'=>'1',
                'password'=> bcrypt('password'),
            ],
            [
                'name'=>'User',
                'email'=>'user@test.com',
                'phone'=>'08128887265',
                'is_admin'=>'0',
                'password'=> bcrypt('password'),
            ],
            [
                'name'=>'User2',
                'email'=>'user2@test.com',
                'phone'=>'08116457878',
                'is_admin'=>'0',
                'password'=> bcrypt('password'),
            ],
        ];
        
        foreach ($user as $key => $value) {
            User::create($value);
        }
    }
}
