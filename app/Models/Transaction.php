<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<TransactionFactory>
 */
final class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
        'total_deducted',
        'status',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * @param  Builder<Transaction>  $query
     */
    #[Scope]
    protected function forUser(Builder $query, int $userId): void
    {
        $query->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_fee' => 'decimal:2',
            'total_deducted' => 'decimal:2',
        ];
    }
}
