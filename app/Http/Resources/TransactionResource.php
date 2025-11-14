<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUserId = $request->user()->id;
        $isSent = $this->sender_id === $currentUserId;

        return [
            'id' => $this->id,
            'type' => $isSent ? 'sent' : 'received',
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'commission_fee' => number_format((float) $this->commission_fee, 2, '.', ''),
            'status' => $this->status,
            'sender' => SenderResource::make($this->sender),
            'receiver' => SenderResource::make($this->receiver),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
