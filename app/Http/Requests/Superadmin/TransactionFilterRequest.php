<?php

namespace App\Http\Requests\Superadmin;

use App\Enums\PaymentMethod;
use App\Enums\VerificationStatus;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Transaction::class);
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'cashier_id' => ['nullable', 'integer', 'exists:users,id'],
            'payment_method' => ['nullable', Rule::in(PaymentMethod::values())],
            'verification_status' => ['nullable', Rule::in(VerificationStatus::values())],
        ];
    }
}
