<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Transaction $resource
 */
final class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();
        $currentUserId = $user->id;
        $isSent = $this->resource->sender_id === $currentUserId;

        return [
            'id' => $this->resource->id,
            'type' => $isSent ? 'sent' : 'received',
            'amount' => number_format((float) $this->resource->amount, 2, '.', ''),
            'commission_fee' => number_format((float) $this->resource->commission_fee, 2, '.', ''),
            'status' => $this->resource->status,
            'sender' => SenderResource::make($this->resource->sender),
            'receiver' => SenderResource::make($this->resource->receiver),
            'created_at' => $this->resource->created_at?->toISOString(),
        ];
    }
}
