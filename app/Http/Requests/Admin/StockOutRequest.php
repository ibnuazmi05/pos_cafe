<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-master-data');
    }

    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', Rule::exists('ingredients', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['note' => 'alasan'];
    }
}
