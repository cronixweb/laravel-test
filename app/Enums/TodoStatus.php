<?php

namespace App\Enums;

enum TodoStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Canceled = 'canceled';

    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }
}
