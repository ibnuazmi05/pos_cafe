<?php

namespace App\Http\Requests\Admin;

use App\Enums\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('transaction'));
    }

    public function rules(): array
    {
        return [
            'verification_status' => ['required', Rule::in([VerificationStatus::Verified->value, VerificationStatus::Flagged->value])],
            'note' => [Rule::requiredIf(fn() => $this->input('verification_status') === VerificationStatus::Flagged->value), 'nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return ['verification_status' => 'status verifikasi', 'note' => 'catatan'];
    }

    public function messages(): array
    {
        return ['note.required' => 'Catatan wajib diisi saat menandai transaksi (jelaskan selisihnya).'];
    }
}
