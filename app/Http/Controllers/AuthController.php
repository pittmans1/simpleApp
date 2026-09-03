<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request, AuditLogService $audit): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'tenant_name' => ['required', 'string', 'max:120'],
        ]);

        [$user, $tenant] = DB::transaction(function () use ($data): array {
            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'password' => Hash::make($data['password']),
            ]);
            $tenant = Tenant::create([
                'name' => $data['tenant_name'],
                'slug' => Str::slug($data['tenant_name']).'-'.Str::lower(Str::random(6)),
            ]);
            $tenant->users()->attach($user, ['role' => 'owner']);

            return [$user, $tenant];
        });

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user, 'tenant' => $tenant], 201);
    }

    public function login(Request $request, AuditLogService $audit): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = Str::transliterate(Str::lower($data['email']).'|'.$request->ip());

        abort_if(RateLimiter::tooManyAttempts($key, 5), 429, 'Too many login attempts.');

        if (! Auth::attempt(['email' => Str::lower($data['email']), 'password' => $data['password']])) {
            RateLimiter::increment($key, 60);

            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return response()->json(['user' => $request->user(), 'tenants' => $request->user()->tenants], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out.']);
    }
}
