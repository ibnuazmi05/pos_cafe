<?php

namespace App\Http\Requests\Superadmin;

use App\Enums\Role;
use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]{6,30}$/'],
            // Role akun Superadmin tidak dapat diubah.
            'role' => $target->isSuperadmin()
                ? ['prohibited']
                : [
                    'required',
                    Rule::in([Role::Admin->value, Role::Kasir->value]),
                    function (string $attribute, mixed $value, \Closure $fail) use ($target) {
                        if ($value !== $target->role->value
                            && Shift::open()->where('user_id', $target->id)->exists()) {
                            $fail('Role tidak dapat diubah selama pegawai masih memiliki shift berjalan.');
                        }
                    },
                ],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama', 'phone' => 'telepon'];
    }
}
