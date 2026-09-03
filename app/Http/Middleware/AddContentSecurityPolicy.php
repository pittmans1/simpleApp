<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class AddContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction()) {
            return $next($request);
        }

        $nonce = base64_encode(random_bytes(32));

        Vite::useCspNonce($nonce);

        $response = $next($request);

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' http://localhost:5173 http://0.0.0.0:5173",
            "style-src 'self' 'nonce-{$nonce}'",
            "connect-src 'self' http://localhost:5173 http://0.0.0.0:5173 ws://localhost:5173 ws://0.0.0.0:5173 ws://localhost:8080",
            "img-src 'self' data:",
            "font-src 'self' data:",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
        ]));

        return $response;
    }
}
