<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@admin.com'], // Find by email first to avoid duplicates
            [
                'name' => 'admin',
                'password' => Hash::make('random pas'), // Use the provided password
                'role' => 'owner',
                'email_verified_at' => now(), // Mark as verified
                'active' => true,
                // 'telegram_id' => null, // Default is null
                // 'team_id' => null, // Default is null
                // 'is_virtual' => false, // Default is false
            ]
        );

        // Create a virtual System user
        User::firstOrCreate(
            ['name' => 'System', 'role' => 'owner'], // Find by name and role
            [
                'is_virtual' => true,
                'active' => true,
                // No email, password, or telegram needed for system user
            ]
        );
    }
} 