<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class TransactionException extends Exception
{
    public static function receiverNotFound(): self
    {
        return new self('Receiver not found.');
    }

    public static function insufficientBalance(float $required, float $available): self
    {
        return new self(
            sprintf(
                'Insufficient balance. You need %s but only have %s.',
                number_format($required, 2),
                number_format($available, 2)
            )
        );
    }

    public static function invalidAmount(): self
    {
        return new self('Invalid transaction amount.');
    }

    public static function senderNotFound(): self
    {
        return new self('Sender not found.');
    }
}
