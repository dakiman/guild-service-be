<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        User::factory()->create([
//            'name' => 'Daki',
//            'email' => 'daki@daki.com'
//        ]);

        User::create([
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'name' => "Daki",
            'email' => 'daki@daki',
            'password' => bcrypt('password'),
        ]);
    }
}
