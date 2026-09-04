<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->whereKey($tenant->getKey())->exists();
    }

    public function manage(User $user, Tenant $tenant): bool
    {
        return $user->tenants()->whereKey($tenant->getKey())->wherePivotIn('role', ['owner', 'admin'])->exists();
    }
}
