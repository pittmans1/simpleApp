<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class TenantNotification extends Model
{
    protected $table = 'tenant_notifications';
    protected $fillable = ['user_id', 'type', 'title', 'body', 'data', 'read_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);
            $context->hasTenant()
                ? $builder->where($builder->getModel()->qualifyColumn('tenant_id'), $context->id())
                : $builder->whereRaw('1 = 0');
        });

        static::creating(function (self $notification): void {
            if (! app(TenantContext::class)->hasTenant()) {
                throw new LogicException('Notifications require a tenant context.');
            }
            $notification->tenant_id = app(TenantContext::class)->id();
        });
    }
}
