<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class CloseShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isKasir();
    }

    public function rules(): array
    {
        return [
            'closing_cash' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['closing_cash' => 'uang tunai di laci', 'note' => 'catatan'];
    }
}
