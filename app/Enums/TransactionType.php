<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match($this) {
            self::Income => 'Ingreso',
            self::Expense => 'Gasto',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Income => 'green',
            self::Expense => 'red',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Income => 'arrow-up',
            self::Expense => 'arrow-down',
        };
    }
}
