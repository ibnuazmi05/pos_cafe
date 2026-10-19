<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Superadmin, Role::Admin);
    }

    public function view(User $user, Shift $shift): bool
    {
        return $user->hasRole(Role::Superadmin, Role::Admin) || $shift->user_id === $user->id;
    }

    public function open(User $user): bool
    {
        return $user->isKasir();
    }

    public function close(User $user, Shift $shift): bool
    {
        return $user->isKasir() && $shift->user_id === $user->id && $shift->isOpen();
    }

    /** Admin dapat menutup paksa shift kasir yang lupa/berhalangan menutup. */
    public function forceClose(User $user, Shift $shift): bool
    {
        return $user->isAdmin() && $shift->isOpen();
    }
}
