<?php

namespace App\Http\Requests\Kasir;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class OpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('open', Shift::class);
    }

    public function rules(): array
    {
        return [
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    public function attributes(): array
    {
        return ['opening_cash' => 'modal awal'];
    }
}
