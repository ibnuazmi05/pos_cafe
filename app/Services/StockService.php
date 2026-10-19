<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Ubah stok sebesar $quantity (bertanda: + masuk, - keluar).
     *
     * @throws InsufficientStockException bila stok akan negatif dan $allowNegative = false
     */
    public function adjust(
        Ingredient|int $ingredient,
        StockMovementType $type,
        float|int|string $quantity,
        ?User $user = null,
        ?Model $reference = null,
        ?string $note = null,
        bool $allowNegative = false,
    ): StockMovement {
        return DB::transaction(function () use ($ingredient, $type, $quantity, $user, $reference, $note, $allowNegative) {
            $id = $ingredient instanceof Ingredient ? $ingredient->getKey() : $ingredient;

            $locked = Ingredient::withTrashed()->whereKey($id)->lockForUpdate()->firstOrFail();

            $delta = round((float) $quantity, 3);
            $after = round((float) $locked->stock + $delta, 3);

            if ($after < 0 && ! $allowNegative) {
                throw new InsufficientStockException($locked->name, (float) $locked->stock, abs($delta), $locked->unit);
            }

            $locked->forceFill(['stock' => $after])->save();

            // Jaga instance milik pemanggil tetap sinkron.
            if ($ingredient instanceof Ingredient) {
                $ingredient->setAttribute('stock', $after);
                $ingredient->syncOriginalAttribute('stock');
            }

            return StockMovement::create([
                'ingredient_id' => $locked->id,
                'type' => $type,
                'quantity' => $delta,
                'stock_after' => $after,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $user?->id ?? auth()->id(),
                'note' => $note,
            ]);
        });
    }

    /** Stok masuk manual (opsional memperbarui harga beli terakhir). */
    public function stockIn(Ingredient $ingredient, float $quantity, ?float $unitCost = null, ?User $user = null, ?string $note = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $quantity, $unitCost, $user, $note) {
            $movement = $this->adjust($ingredient, StockMovementType::In, abs($quantity), $user, null, $note);

            if ($unitCost !== null) {
                $ingredient->update(['last_cost' => $unitCost]);
            }

            ActivityLogger::log('stock.in', $ingredient, [
                'quantity' => $quantity,
                'stock_after' => $movement->stock_after,
                'note' => $note,
            ], $user);

            return $movement;
        });
    }

    /** Stok keluar manual (rusak, kedaluwarsa, pemakaian lain). Wajib cukup. */
    public function stockOut(Ingredient $ingredient, float $quantity, ?User $user = null, ?string $note = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $quantity, $user, $note) {
            $movement = $this->adjust($ingredient, StockMovementType::Out, -abs($quantity), $user, null, $note);

            ActivityLogger::log('stock.out', $ingredient, [
                'quantity' => $quantity,
                'stock_after' => $movement->stock_after,
                'note' => $note,
            ], $user);

            return $movement;
        });
    }

    /** Stok opname sederhana: set stok = hasil hitung fisik, selisih dicatat. */
    public function opname(Ingredient $ingredient, float $counted, ?User $user = null, ?string $note = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $counted, $user, $note) {
            $current = (float) Ingredient::whereKey($ingredient->getKey())->lockForUpdate()->value('stock');
            $difference = round($counted - $current, 3);

            $movement = $this->adjust($ingredient, StockMovementType::Opname, $difference, $user, null, $note);

            ActivityLogger::log('stock.opname', $ingredient, [
                'before' => $current,
                'counted' => $counted,
                'difference' => $difference,
                'note' => $note,
            ], $user);

            return $movement;
        });
    }

    /**
     *
     * @param  array<int, float|int|string>  $requirements
     *
     * @throws InsufficientStockException
     */
    public function consume(array $requirements, Model $reference, ?User $user = null): void
    {
        ksort($requirements);

        DB::transaction(function () use ($requirements, $reference, $user) {
            foreach ($requirements as $ingredientId => $quantity) {
                $this->adjust((int) $ingredientId, StockMovementType::Sale, -abs((float) $quantity), $user, $reference);
            }
        });
    }
}
