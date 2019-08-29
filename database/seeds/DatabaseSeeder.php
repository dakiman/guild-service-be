<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\User::create([
            'email' => 'daki@daki.com',
            'name' => 'daki',
            'password' => bcrypt('password')
        ]);
    }
}
