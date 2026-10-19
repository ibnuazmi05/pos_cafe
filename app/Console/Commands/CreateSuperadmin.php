<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateSuperadmin extends Command
{
    protected $signature = 'pos:create-superadmin {--name=} {--email=} {--password=}';

    protected $description = 'Buat akun Superadmin (pemilik cafe). Dipakai saat instalasi production tanpa seeder demo.';

    public function handle(): int
    {
        $input = [
            'name' => $this->option('name') ?: $this->ask('Nama'),
            'email' => $this->option('email') ?: $this->ask('Email'),
            // Lebih aman memakai prompt: opsi --password tersimpan di riwayat shell.
            'password' => $this->option('password') ?: $this->secret('Password (minimal 8 karakter)'),
        ];

        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create($input + ['role' => Role::Superadmin, 'is_active' => true]);

        $this->info("Superadmin {$user->email} berhasil dibuat.");

        return self::SUCCESS;
    }
}
