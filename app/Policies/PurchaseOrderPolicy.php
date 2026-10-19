<?php

namespace App\Policies;

use App\Enums\PurchaseOrderStatus;
use App\Enums\Role;
use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Superadmin, Role::Admin);
    }

    public function view(User $user, PurchaseOrder $po): bool
    {
        return $user->hasRole(Role::Superadmin, Role::Admin);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Edit/hapus hanya saat masih draft. */
    public function update(User $user, PurchaseOrder $po): bool
    {
        return $user->isAdmin() && $po->status === PurchaseOrderStatus::Draft;
    }

    public function delete(User $user, PurchaseOrder $po): bool
    {
        return $this->update($user, $po);
    }

    public function order(User $user, PurchaseOrder $po): bool
    {
        return $user->isAdmin() && $po->status === PurchaseOrderStatus::Draft;
    }

    public function receive(User $user, PurchaseOrder $po): bool
    {
        return $user->isAdmin() && $po->status === PurchaseOrderStatus::Ordered;
    }

    public function cancel(User $user, PurchaseOrder $po): bool
    {
        return $user->isAdmin()
            && in_array($po->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Ordered], true);
    }
}
