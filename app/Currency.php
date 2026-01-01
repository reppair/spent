<?php

namespace App;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';

    public function sign(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD => '$',
        };
    }
}
