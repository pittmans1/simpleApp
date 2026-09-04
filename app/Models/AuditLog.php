<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'ip_address', 'user_agent', 'metadata', 'created_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);
            $context->hasTenant()
                ? $builder->where($builder->getModel()->qualifyColumn('tenant_id'), $context->id())
                : $builder->whereRaw('1 = 0');
        });

        static::creating(function (self $log): void {
            if (! app(TenantContext::class)->hasTenant()) {
                throw new LogicException('Audit logs require a tenant context.');
            }
            $log->tenant_id = app(TenantContext::class)->id();
        });
    }
}
