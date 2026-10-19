<?php

namespace App\Exceptions;

use DomainException;

class InsufficientStockException extends DomainException
{
    public function __construct(
        public readonly string $ingredientName,
        public readonly float $available,
        public readonly float $required,
        public readonly string $unit = '',
    ) {
        parent::__construct(sprintf(
            'Stok %s tidak cukup (tersedia %s %s, dibutuhkan %s %s).',
            $ingredientName,
            rtrim(rtrim(number_format($available, 3, ',', '.'), '0'), ','),
            $unit,
            rtrim(rtrim(number_format($required, 3, ',', '.'), '0'), ','),
            $unit,
        ));
    }
}
