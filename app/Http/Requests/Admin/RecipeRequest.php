<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-master-data');
    }

    public function rules(): array
    {
        return [
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.ingredient_id' => ['required', 'distinct', Rule::exists('ingredients', 'id')->whereNull('deleted_at')],
            'ingredients.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'ingredients.*.ingredient_id.distinct' => 'Bahan yang sama tidak boleh dipilih dua kali.',
            'ingredients.*.ingredient_id.required' => 'Pilih bahan pada setiap baris resep.',
            'ingredients.*.quantity.required' => 'Isi jumlah pada setiap baris resep.',
            'ingredients.*.quantity.gt' => 'Jumlah bahan harus lebih dari 0.',
        ];
    }
}
