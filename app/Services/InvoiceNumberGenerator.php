<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\CarbonInterface;

/**
 * Format INV-YYYYMMDD-0001. Harus dipanggil di dalam DB::transaction:
 * baris invoice terakhir dikunci sehingga checkout bersamaan terserialisasi.
 * Kolom invoice_no yang unik menjadi pengaman akhir (CheckoutService mengulang bila bentrok).
 */
class InvoiceNumberGenerator
{
    public function next(?CarbonInterface $date = null): string
    {
        $prefix = 'INV-'.($date ?? now())->format('Ymd').'-';

        $last = Transaction::withTrashed()
            ->where('invoice_no', 'like', $prefix.'%')
            ->orderByDesc('invoice_no')
            ->lockForUpdate()
            ->value('invoice_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
