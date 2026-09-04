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
        $tenant = $request->user()?->tenants()->where('slug', (string) $request->route('tenant'))->first();
        abort_unless($tenant, 404);

        app(TenantContext::class)->set($tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
