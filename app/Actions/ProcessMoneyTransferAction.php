<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\TransactionProcessed;
use App\Exceptions\TransactionException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class ProcessMoneyTransferAction
{
    /**
     * @throws TransactionException
     * @throws Throwable
     */
    public function handle(User $sender, int $receiverId, float $amount): Transaction
    {
        $transaction = DB::transaction(function () use ($sender, $receiverId, $amount) {
            $lockedSender = User::query()
                ->where('id', $sender->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSender) {
                throw TransactionException::senderNotFound();
            }

            $receiver = User::query()
                ->where('id', $receiverId)
                ->lockForUpdate()
                ->first();

            if (! $receiver) {
                throw TransactionException::receiverNotFound();
            }

            $commission = round($amount * config('wallet.commission_rate'), 2);
            $totalDeducted = $amount + $commission;

            if ($lockedSender->balance < $totalDeducted) {
                throw TransactionException::insufficientBalance($totalDeducted, $lockedSender->balance);
            }

            $lockedSender->decrement('balance', $totalDeducted);
            $receiver->increment('balance', $amount);

            return Transaction::query()->create([
                'sender_id' => $lockedSender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commission,
                'total_deducted' => $totalDeducted,
                'status' => 'completed',
            ]);
        });

        event(new TransactionProcessed($transaction));

        return $transaction;
    }
}
