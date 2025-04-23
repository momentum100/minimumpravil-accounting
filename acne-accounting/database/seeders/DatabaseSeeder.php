<?php

namespace Database\Seeders;

// use App\Models\User; // Commented out if default user factory is not needed
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class, // Creates Admin and System users
            SystemAccountsSeeder::class, // Creates system accounts linked to System user
            // Add other seeders here if needed
        ]);

        // Remove or keep default user factory call as needed
        // User::factory(10)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
