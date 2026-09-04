<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class DashboardWidget extends Model
{
    protected $fillable = ['key', 'title', 'type', 'configuration', 'position'];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);
            $context->hasTenant()
                ? $builder->where($builder->getModel()->qualifyColumn('tenant_id'), $context->id())
                : $builder->whereRaw('1 = 0');
        });

        static::creating(function (self $widget): void {
            if (! app(TenantContext::class)->hasTenant()) {
                throw new LogicException('Dashboard widgets require a tenant context.');
            }
            $widget->tenant_id = app(TenantContext::class)->id();
        });
    }

    protected function casts(): array
    {
        return ['configuration' => 'array', 'position' => 'integer'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
