<?php

namespace Database\Seeders;
use App\Livewire\Admin\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

     
// To this:
User::factory()->create([
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com',
    'phone' => '09012345678', // Add any other required fields
    'role' => 0,              // 0 for user, as per your earlier migration
]);

    }
}
