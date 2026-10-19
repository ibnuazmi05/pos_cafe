<?php

namespace App\Http\Requests\Admin;

use App\Models\PurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $po = $this->route('purchaseOrder');

        return $po
            ? $this->user()->can('update', $po)
            : $this->user()->can('create', PurchaseOrder::class);
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'distinct', Rule::exists('ingredients', 'id')->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ];
    }

    public function attributes(): array
    {
        return ['supplier_id' => 'supplier', 'note' => 'catatan'];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Tambahkan minimal satu bahan.',
            'items.min' => 'Tambahkan minimal satu bahan.',
            'items.*.ingredient_id.required' => 'Pilih bahan pada setiap baris.',
            'items.*.ingredient_id.distinct' => 'Bahan yang sama tidak boleh dipilih dua kali.',
            'items.*.quantity.required' => 'Isi jumlah pada setiap baris.',
            'items.*.quantity.gt' => 'Jumlah harus lebih dari 0.',
            'items.*.unit_price.required' => 'Isi harga satuan pada setiap baris.',
        ];
    }
}
