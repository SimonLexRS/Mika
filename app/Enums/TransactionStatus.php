<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
        };
    }
}
