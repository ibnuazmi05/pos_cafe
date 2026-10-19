<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ForceCloseShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('forceClose', $this->route('shift'));
    }

    public function rules(): array
    {
        return [
            'closing_cash' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'note' => ['required', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['closing_cash' => 'uang tunai di laci', 'note' => 'alasan penutupan paksa'];
    }
}
