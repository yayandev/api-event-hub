<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Yayan Faturrohman',
            'email' => 'yayanfathurohman20@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Angga',
            'email' => 'angga123@gmail.com',
            'role' => 'organizer',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Budi',
            'email' => 'budi123@gmail.com',
            'role' => 'customer',
            'password' => bcrypt('password'),
        ]);

        Category::factory()->create([
            'name' => 'OFFLINE',
            'slug' => 'offline'
        ]);

        Category::factory()->create([
            'name' => 'ONLINE',
            'slug' => 'online'
        ]);
    }
}
