<?php

namespace App\Support;

class Format
{
    public static function rupiah(int|float|string|null $amount): string
    {
        $amount = (float) $amount;

        return ($amount < 0 ? '-' : '').'Rp '.number_format(abs($amount), 0, ',', '.');
    }

    /** Jumlah bahan tanpa nol di belakang koma: 18.000 => "18", 1.5 => "1,5". */
    public static function qty(int|float|string|null $value): string
    {
        $formatted = number_format((float) $value, 3, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }

    /** Pola LIKE aman: wildcard dari input pengguna di-escape. */
    public static function like(string $value): string
    {
        return '%'.addcslashes($value, '%_\\').'%';
    }
}
