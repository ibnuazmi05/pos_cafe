<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\VerificationStatus;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\Transaction;
use App\Models\User;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class CheckoutService
{
    public function __construct(
        private StockService $stock,
        private InvoiceNumberGenerator $invoices,
    ) {}

    /**
     * @param array{items: array<int, array{product_id:int, quantity:int, note?:?string}>,
     *              apply_discount?:bool,
     *              payment_method:string, paid_amount?:float|int|string|null} $data
     *
     * @throws ValidationException  aturan bisnis dilanggar (diskon, uang kurang, menu tak tersedia)
     * @throws DomainException      shift tidak aktif / stok bahan tidak cukup (InsufficientStockException)
     */
    public function checkout(User $cashier, Shift $shift, array $data): Transaction
    {
        $attempts = 0;

        while (true) {
            try {
                return DB::transaction(fn() => $this->process($cashier, $shift, $data));
            } catch (UniqueConstraintViolationException $e) {
                if (++$attempts >= 3) {
                    throw $e;
                }
            }
        }
    }

    private function process(User $cashier, Shift $shift, array $data): Transaction
    {
        $lockedShift = Shift::whereKey($shift->getKey())->lockForUpdate()->firstOrFail();

        if (! $lockedShift->isOpen() || $lockedShift->user_id !== $cashier->id) {
            throw new DomainException('Shift tidak aktif. Buka shift terlebih dahulu.');
        }

        $lines = collect($data['items']);

        $products = Product::active()
            ->with('ingredients')
            ->whereIn('id', $lines->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        foreach ($lines as $line) {
            if (! $products->has((int) $line['product_id'])) {
                throw ValidationException::withMessages([
                    'items' => 'Ada menu yang sudah tidak tersedia. Muat ulang halaman POS.',
                ]);
            }
        }

        $subtotal = 0.0;
        $itemRows = [];
        $requirements = [];

        foreach ($lines as $line) {
            $product = $products[(int) $line['product_id']];
            $quantity = (int) $line['quantity'];
            $price = (float) $product->price;
            $lineTotal = round($price * $quantity, 2);

            $subtotal += $lineTotal;

            $itemRows[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $price,
                'quantity' => $quantity,
                'subtotal' => $lineTotal,
                'note' => $line['note'] ?? null,
            ];

            foreach ($product->ingredients as $ingredient) {
                $requirements[$ingredient->id] = ($requirements[$ingredient->id] ?? 0)
                    + (float) $ingredient->pivot->quantity * $quantity;
            }
        }

        $subtotal = round($subtotal, 2);

        // 2. Diskon, pajak, total
        $discount = $this->discount($subtotal, $data);
        $taxBase = $subtotal - $discount;

        $taxEnabled = Setting::get('tax_enabled', '0') === '1';
        $taxRate = $taxEnabled ? (float) Setting::get('tax_rate', 0) : 0.0;
        $tax = round($taxBase * $taxRate / 100, 0);
        $rawTotal = round($taxBase + $tax, 2);
        $roundingConfig = (float) Setting::get('rounding', 500);
        $roundingConfig = $roundingConfig > 0 ? $roundingConfig : 500;
        $total = round($rawTotal / $roundingConfig) * $roundingConfig;
        $rounding = round($total - $rawTotal, 2);

        logger()->debug('Pemeriksaan rounding', [
            'taxBase' => $taxBase,
            'tax' => $tax,
            'rawTotal' => $rawTotal,
            'roundingConfig' => $roundingConfig,
            'total' => $total,
            'rounding' => $rounding,
        ]);

        // 3. Pembayaran
        $method = PaymentMethod::from($data['payment_method']);

        if ($method === PaymentMethod::Cash) {
            $paid = round((float) ($data['paid_amount'] ?? 0), 2);

            if ($paid + 0.005 < $total) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Uang yang diterima kurang dari total pembayaran.',
                ]);
            }

            $change = round($paid - $total, 2);
        } else {
            $paid = $total;
            $change = 0.0;
        }

        // 4. Simpan transaksi + item
        $transaction = Transaction::create([
            'invoice_no' => $this->invoices->next(),
            'cashier_id' => $cashier->id,
            'shift_id' => $lockedShift->id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'rounding' => $rounding,
            'total' => $total,
            'payment_method' => $method,
            'paid_amount' => $paid,
            'change_amount' => $change,
            'verification_status' => VerificationStatus::Pending,
        ]);

        $transaction->items()->createMany($itemRows);

        // 5. Kurangi stok (melempar InsufficientStockException bila kurang -> semua di-rollback)
        $this->stock->consume($requirements, $transaction, $cashier);

        return $transaction->load('items');
    }

    private function discount(float $subtotal, array $data): float
    {
        if (empty($data['apply_discount'])) {
            return 0.0;
        }

        $percent = (float) Setting::get('discount', 0);

        if ($percent <= 0) {
            return 0.0;
        }

        return min(round($subtotal * $percent / 100, 0), $subtotal);
    }
}
