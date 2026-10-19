<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-master-data');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('ingredients', 'name')->ignore($this->route('ingredient'))],
            'unit' => ['required', 'string', 'max:20'],
            'min_stock' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'last_cost' => ['required', 'numeric', 'min:0', 'max:99999999'],
            // Stok awal hanya saat membuat bahan baru; setelahnya lewat menu Stok.
            'initial_stock' => [$this->isMethod('POST') ? 'nullable' : 'prohibited', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama bahan', 'unit' => 'satuan', 'min_stock' => 'stok minimum', 'last_cost' => 'harga beli', 'initial_stock' => 'stok awal'];
    }
}
