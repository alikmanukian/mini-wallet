<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
final class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 10, 1000);
        /** @var float $commissionRate */
        $commissionRate = config('wallet.commission_rate');
        $commissionFee = $amount * $commissionRate;

        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'amount' => $amount,
            'commission_fee' => $commissionFee,
            'status' => 'completed',
        ];
    }
}
