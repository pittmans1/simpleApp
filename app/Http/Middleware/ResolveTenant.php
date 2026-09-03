<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $slug = (string) $request->route('tenant');
        $tenant = $user?->tenants()->where('slug', $slug)->first();

        abort_unless($tenant, 404);

        app(TenantContext::class)->set($tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
