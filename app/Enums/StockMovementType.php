<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum StockMovementType: string
{
    use HasValues;

    case In = 'in';
    case Out = 'out';
    case Opname = 'opname';
    case Sale = 'sale';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Stok Masuk',
            self::Out => 'Stok Keluar',
            self::Opname => 'Stok Opname',
            self::Sale => 'Penjualan',
        };
    }
}
