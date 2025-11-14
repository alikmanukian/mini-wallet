<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUser = User::factory()->withoutTwoFactor()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'balance' => 5000.00,
        ]);

        $otherUsers = User::factory()->withoutTwoFactor()->count(9)->create();

        foreach ($otherUsers as $user) {
            Transaction::factory()->count(rand(2, 5))->create([
                'sender_id' => $testUser->id,
                'receiver_id' => $user->id,
            ]);

            Transaction::factory()->count(rand(1, 3))->create([
                'sender_id' => $user->id,
                'receiver_id' => $testUser->id,
            ]);
        }
    }
}
