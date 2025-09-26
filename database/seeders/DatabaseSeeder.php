<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->count(3)->create();

        $user = \App\Models\User::factory()->create([
            'name' => 'Demo User',
            'last_name' => 'Test',
            'email' => 'demo@example.com',
            'password' => bcrypt('12345678'),
        ]);

        \App\Models\Task::factory()->count(15)->create([
            'user_id' => $user->id,
        ]);
    }
}
