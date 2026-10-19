<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-settings');
    }

    public function rules(): array
    {
        return [
            'cafe_name' => ['required', 'string', 'max:100'],
            'cafe_address' => ['nullable', 'string', 'max:255'],
            'tax_enabled' => ['sometimes', 'boolean'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'discount' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cafe_name' => 'nama cafe',
            'cafe_address' => 'alamat',
            'tax_rate' => 'tarif pajak',
            'discount' => 'potongan harga',
        ];
    }
}
