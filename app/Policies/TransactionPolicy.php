<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->hasRole(Role::Superadmin, Role::Admin)) {
            return true;
        }

        return $user->isKasir() && $transaction->cashier_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isKasir();
    }
    public function verify(User $user, Transaction $transaction): bool
    {
        return $user->hasRole(Role::Superadmin, Role::Admin);
    }
    public function update(User $user, Transaction $transaction): bool
    {
        return false;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return false;
    }
}
