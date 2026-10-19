<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ShiftStatus: string
{
    use HasValues;

    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Berjalan',
            self::Closed => 'Ditutup',
        };
    }
}
