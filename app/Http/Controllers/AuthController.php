<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const ACHIEVEMENT_KEYS = [
        'hello-world', 'trash-taste', 'three-clicks', 'night-shift', 'tiny-bin', 'corner-creep',
        'scroll-scout', 'keyboard-bandit', 'footer-foodie', 'panda-paparazzi', 'sneaky-swipe',
        'moonwalk', 'lucky-seven', 'recycle-raccoon', 'deep-dive', 'trash-treasure',
        'full-den', 'panda-pro',
    ];

    public function achievements(Request $request): JsonResponse
    {
        return response()->json(['achievements' => $request->user()->achievements ?? []]);
    }

    public function addAchievement(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'in:'.implode(',', self::ACHIEVEMENT_KEYS)],
        ]);
        $user = $request->user();
        $achievements = array_values(array_unique($user->achievements ?? []));

        if (! in_array($data['key'], $achievements, true)) {
            $achievements[] = $data['key'];
            $user->update(['achievements' => $achievements]);
        }

        return response()->json(['achievements' => $achievements]);
    }

    public function theme(Request $request): JsonResponse
    {
        return response()->json(['theme' => $request->user()->theme]);
    }

    public function updateTheme(Request $request): JsonResponse
    {
        $data = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,trashpanda'],
        ]);

        $request->user()->update(['theme' => $data['theme']]);

        return response()->json(['theme' => $request->user()->theme]);
    }

    public function register(Request $request, AuditLogService $audit): JsonResponse|RedirectResponse
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

        if (! $request->expectsJson()) {
            return redirect()->route('tenant.dashboard', ['tenant' => $tenant->slug]);
        }

        return response()->json(['user' => $user, 'tenant' => $tenant], 201);
    }

    public function login(Request $request, AuditLogService $audit): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = Str::transliterate(Str::lower($data['email']).'|'.$request->ip());

        abort_if(RateLimiter::tooManyAttempts($key, 5), 429, 'Too many login attempts.');

        if (! Auth::attempt(['email' => Str::lower($data['email']), 'password' => $data['password']])) {
            RateLimiter::increment($key, 60);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid credentials.'], 422);
            }

            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        if (! $request->expectsJson()) {
            return $this->authenticatedRedirect($request->user());
        }

        return response()->json(['user' => $request->user(), 'tenants' => $request->user()->tenants], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (! $request->expectsJson()) {
            return response()->json(['message' => 'Logged out.']);
        }

        return response()->json(['message' => 'Logged out.']);
    }

    private function authenticatedRedirect(User $user): RedirectResponse
    {
        $tenant = $user->tenants()->orderBy('tenants.name')->first();

        return $tenant
            ? redirect()->route('tenant.dashboard', ['tenant' => $tenant->slug])
            : redirect()->route('dashboard');
    }
}
