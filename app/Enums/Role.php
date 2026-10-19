<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum Role: string
{
    use HasValues;

    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Kasir = 'kasir';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin (Pemilik)',
            self::Admin => 'Admin',
            self::Kasir => 'Kasir',
        };
    }

    /** Nama route dashboard untuk redirect setelah login. */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Superadmin => 'superadmin.dashboard',
            self::Admin => 'admin.dashboard',
            self::Kasir => 'kasir.shift.index',
        };
    }
}
