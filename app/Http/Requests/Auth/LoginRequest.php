<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = (string) $this->input('email');
        $password = (string) $this->input('password');

        // Pegawai nonaktif + password benar: beri pesan spesifik, tetap dihitung percobaan.
        $user = User::where('email', $email)->first();

        if ($user && ! $user->is_active && Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());
            ActivityLogger::log('auth.login_blocked', $user, ['reason' => 'inactive'], $user);

            throw ValidationException::withMessages([
                'email' => 'Akun Anda dinonaktifkan. Hubungi pemilik cafe.',
            ]);
        }

        if (! Auth::attempt(
            ['email' => $email, 'password' => $password, 'is_active' => true],
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());
            ActivityLogger::log('auth.login_failed', null, ['email' => $email]);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('email')).'|'.$this->ip());
    }
}
