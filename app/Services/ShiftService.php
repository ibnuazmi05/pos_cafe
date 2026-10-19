<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\ShiftStatus;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;


class ShiftService
{
    public function open(User $cashier, float $openingCash): Shift
    {
        return DB::transaction(function () use ($cashier, $openingCash) {
            // Kunci baris user agar dua klik "buka shift" tidak membuat dua shift.
            User::whereKey($cashier->id)->lockForUpdate()->firstOrFail();

            if (Shift::open()->where('user_id', $cashier->id)->exists()) {
                throw new DomainException('Anda masih memiliki shift yang berjalan.');
            }

            $shift = Shift::create([
                'user_id' => $cashier->id,
                'opened_at' => now(),
                'opening_cash' => round($openingCash, 2),
                'status' => ShiftStatus::Open,
            ]);

            ActivityLogger::log('shift.opened', $shift, ['opening_cash' => $shift->opening_cash], $cashier);

            return $shift;
        });
    }

    /**
     * @throws DomainException bila shift sudah ditutup, atau (bukan paksa) masih ada void/refund pending
     */
    public function close(Shift $shift, User $actor, float $closingCash, ?string $note = null, bool $force = false): Shift
    {
        return DB::transaction(function () use ($shift, $actor, $closingCash, $note, $force) {
            $locked = Shift::whereKey($shift->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isOpen()) {
                throw new DomainException('Shift ini sudah ditutup.');
            }

            $expected = $this->expectedCash($locked);
            $closing = round($closingCash, 2);
            $difference = round($closing - $expected, 2);

            if ($force) {
                $note = trim('[Ditutup paksa oleh ' . $actor->name . '] ' . ($note ?? ''));
            }

            $locked->update([
                'status' => ShiftStatus::Closed,
                'closed_at' => now(),
                'expected_cash' => $expected,
                'closing_cash' => $closing,
                'difference' => $difference,
                'note' => $note,
            ]);

            ActivityLogger::log($force ? 'shift.force_closed' : 'shift.closed', $locked, [
                'expected_cash' => $expected,
                'closing_cash' => $closing,
                'difference' => $difference,
                'note' => $note,
            ], $actor);

            return $locked;
        });
    }

    public function expectedCash(Shift $shift): float
    {
        return round((float) $shift->opening_cash + $this->cashSales($shift), 2);
    }

    public function cashSales(Shift $shift): float
    {
        return (float) Transaction::query()
            ->where('shift_id', $shift->id)
            ->where('payment_method', PaymentMethod::Cash->value)
            ->sum('total');
    }

    /** Ringkasan untuk halaman shift. */
    public function summary(Shift $shift): array
    {
        $rows = Transaction::query()
            ->where('shift_id', $shift->id)
            ->selectRaw('payment_method, COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->groupBy('payment_method')
            ->get();

        $byMethod = [];
        foreach (PaymentMethod::cases() as $method) {
            $row = $rows->first(fn($r) => $r->payment_method === $method);
            $byMethod[$method->value] = [
                'label' => $method->label(),
                'count' => (int) ($row->count ?? 0),
                'total' => (float) ($row->total ?? 0),
            ];
        }

        return [
            'transactions' => (int) $rows->sum('count'),
            'sales' => (float) $rows->sum('total'),
            'by_method' => $byMethod,
            'expected_cash' => $this->expectedCash($shift),
        ];
    }
}
