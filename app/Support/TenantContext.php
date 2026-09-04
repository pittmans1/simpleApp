<?php

namespace App\Support;

use App\Models\Tenant;
use LogicException;

final class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): Tenant
    {
        return $this->tenant ?? throw new LogicException('A tenant context is required.');
    }

    public function id(): int
    {
        return $this->get()->getKey();
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }
}
