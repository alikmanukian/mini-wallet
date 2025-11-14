<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\TransactionException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class ProcessMoneyTransferAction
{
    public function __construct(
        private CalculateTransactionCommissionAction $calculateCommission
    ) {}

    public function handle(User $sender, int $receiverId, float $amount): Transaction
    {
        return DB::transaction(function () use ($sender, $receiverId, $amount) {
            $lockedSender = User::query()
                ->where('id', $sender->id)
                ->lockForUpdate()
                ->first();

            $receiver = User::query()
                ->where('id', $receiverId)
                ->lockForUpdate()
                ->first();

            if (! $receiver) {
                throw TransactionException::receiverNotFound();
            }

            $commission = $this->calculateCommission->handle($amount);
            $totalDeducted = $amount + $commission;

            if ($lockedSender->balance < $totalDeducted) {
                throw TransactionException::insufficientBalance($totalDeducted, $lockedSender->balance);
            }

            $lockedSender->decrement('balance', $totalDeducted);
            $receiver->increment('balance', $amount);

            return Transaction::create([
                'sender_id' => $lockedSender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commission,
                'total_deducted' => $totalDeducted,
                'status' => 'completed',
            ]);
        });
    }
}
