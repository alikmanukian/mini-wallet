<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final readonly class TransactionRepository
{
    public function getUserTransactions(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Transaction::query()
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])->latest()
            ->paginate($perPage);
    }

    public function getRecipients(User $currentUser): Collection
    {
        return User::query()
            ->where('id', '!=', $currentUser->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
