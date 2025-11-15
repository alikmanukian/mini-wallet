<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\SenderResource;
use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TransactionProcessed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Transaction $transaction
    ) {
        $this->transaction->load(['sender', 'receiver']);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->transaction->receiver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.processed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'transaction' => [
                'id' => $this->transaction->id,
                'sender_id' => $this->transaction->sender_id,
                'receiver_id' => $this->transaction->receiver_id,
                'amount' => $this->transaction->amount,
                'commission_fee' => $this->transaction->commission_fee,
                'total_deducted' => $this->transaction->total_deducted,
                'status' => $this->transaction->status,
                'created_at' => $this->transaction->created_at?->toISOString(),
                'sender' => SenderResource::make($this->transaction->sender)->resolve(),
                'receiver' => SenderResource::make($this->transaction->receiver)->resolve(),
            ],
        ];
    }
}
