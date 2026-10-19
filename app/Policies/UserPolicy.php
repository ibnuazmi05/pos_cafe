<?php

namespace App\Policies;

use App\Models\User;

/** Manajemen pegawai: eksklusif Superadmin. Admin tidak boleh menambah/menghapus pegawai. */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperadmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isSuperadmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperadmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isSuperadmin();
    }

    // public function resetPassword(User $user, User $target): bool
    // {
    //     return $user->isSuperadmin() && $user->id !== $target->id;
    // }

    public function toggleActive(User $user, User $target): bool
    {
        return $user->isSuperadmin() && $user->id !== $target->id;
    }

    public function delete(User $user, User $target): bool
    {
        return false;
    }
}
