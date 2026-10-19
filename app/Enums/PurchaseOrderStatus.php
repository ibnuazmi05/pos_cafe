<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum PurchaseOrderStatus: string
{
    use HasValues;

    case Draft = 'draft';
    case Ordered = 'ordered';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Ordered => 'Dipesan',
            self::Received => 'Diterima',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
