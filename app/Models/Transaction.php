<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'cashier_id',
        'shift_id',
        'subtotal',
        'discount',
        'tax_rate',
        'tax',
        'rounding',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'verification_status',
        'verified_by',
        'verified_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax' => 'decimal:2',
            'rounding' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Filter dipakai Superadmin/Admin: date_from, date_to, cashier_id,
     * status, payment_method, verification_status.
     */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['date_from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($f['date_to'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($f['cashier_id'] ?? null, fn($q, $v) => $q->where('cashier_id', $v))
            ->when($f['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($f['payment_method'] ?? null, fn($q, $v) => $q->where('payment_method', $v))
            ->when($f['verification_status'] ?? null, fn($q, $v) => $q->where('verification_status', $v));
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id')->withTrashed();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by')->withTrashed();
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
