<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password default: "password". WAJIB diganti sebelum production.
        $users = [
            ['Pemilik Cafe', 'superadmin@cafe.test', Role::Superadmin, '081200000001'],
            ['Admin Cafe', 'admin@cafe.test', Role::Admin, '081200000002'],
            ['Kasir Satu', 'kasir@cafe.test', Role::Kasir, '081200000003'],
        ];

        foreach ($users as [$name, $email, $role, $phone]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password', // di-hash otomatis oleh cast 'hashed'
                    'role' => $role,
                    'phone' => $phone,
                    'is_active' => true,
                ]
            );
        }
    }
}
