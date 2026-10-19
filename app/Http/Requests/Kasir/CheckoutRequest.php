<?php

namespace App\Http\Requests\Kasir;

use App\Enums\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Transaction::class);
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where('is_active', true)->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.note' => ['nullable', 'string', 'max:120'],

            'apply_discount' => ['sometimes', 'boolean'],

            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'paid_amount' => ['nullable', 'required_if:payment_method,cash', 'numeric', 'min:0', 'max:999999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'items' => 'keranjang',
            'apply_discount' => 'diskon',
            'payment_method' => 'metode pembayaran',
            'paid_amount' => 'uang diterima',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang masih kosong.',
            'items.min' => 'Keranjang masih kosong.',
            'items.*.product_id.exists' => 'Ada menu yang sudah tidak tersedia. Muat ulang halaman POS.',
            'items.*.product_id.distinct' => 'Menu yang sama muncul dua kali di keranjang.',
        ];
    }
}
