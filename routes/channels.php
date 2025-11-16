<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', fn ($user, $userId): bool => (int) $user->id === (int) $userId);
