<?php

namespace App\Http\Requests\Reports;

use App\Enums\PurchaseOrderStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view-reports');
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', Rule::in([...PurchaseOrderStatus::values(), 'all'])],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->from()->diffInDays($this->to()) > 366) {
                $validator->errors()->add('date_to', 'Rentang laporan maksimal 1 tahun.');
            }
        });
    }

    /** Default: awal bulan berjalan. */
    public function from(): Carbon
    {
        return Carbon::parse($this->input('date_from') ?: now()->startOfMonth())->startOfDay();
    }

    /** Default: hari ini. */
    public function to(): Carbon
    {
        return Carbon::parse($this->input('date_to') ?: now())->endOfDay();
    }

    public function status(): ?string
    {
        return $this->input('status') ?: null;
    }

    public function supplierId(): ?int
    {
        return $this->filled('supplier_id') ? (int) $this->input('supplier_id') : null;
    }
}
