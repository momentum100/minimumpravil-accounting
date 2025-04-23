<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Log;

class SystemAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the virtual system user created by AdminUserSeeder
        $systemUser = User::where('name', 'System')->where('is_virtual', true)->first();

        if (! $systemUser) {
            Log::error('System user not found in SystemAccountsSeeder. Make sure AdminUserSeeder runs first.');
            $this->command->error('System user not found. Cannot create system accounts.');
            return; // Stop seeding if system user doesn't exist
        }

        // Create System Company Account
        Account::firstOrCreate(
            [
                'user_id' => $systemUser->id,
                'account_type' => 'SYSTEM_COMPANY',
                'currency' => 'USD'
            ],
            [
                'description' => 'Company Main USD Account'
            ]
        );

         // Create System Operations Account
        Account::firstOrCreate(
            [
                'user_id' => $systemUser->id,
                'account_type' => 'SYSTEM_OPERATIONS',
                'currency' => 'USD'
            ],
            [
                'description' => 'System Operations USD Account'
            ]
        );
    }
}
