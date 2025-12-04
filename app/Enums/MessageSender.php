<?php

namespace App\Enums;

enum MessageSender: string
{
    case User = 'user';
    case Bot = 'bot';

    public function label(): string
    {
        return match($this) {
            self::User => 'Usuario',
            self::Bot => 'Mika',
        };
    }
}
