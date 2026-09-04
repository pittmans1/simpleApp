<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'twitter', 'linkedin'];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);
        $socialUser = $driver->user();
        abort_unless($socialUser->getEmail(), 422, 'The provider did not return an email address.');

        $user = User::where('oauth_provider', $provider)->where('oauth_id', $socialUser->getId())->first()
            ?? User::where('email', Str::lower($socialUser->getEmail()))->first();
        $attributes = [
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'SSO User',
            'email' => Str::lower($socialUser->getEmail()),
            'email_verified_at' => now(),
            'oauth_provider' => $provider,
            'oauth_id' => $socialUser->getId(),
            'oauth_token' => $socialUser->token,
            'oauth_refresh_token' => $socialUser->refreshToken,
            'oauth_token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
            'avatar_url' => $socialUser->getAvatar(),
        ];

        if (! $user) {
            $attributes['password'] = Hash::make(Str::random(48));
            $user = User::create($attributes);
        } else {
            $user->update($attributes);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        $tenant = $user->tenants()->orderBy('tenants.name')->first();

        return $tenant ? redirect()->route('tenant.dashboard', ['tenant' => $tenant->slug]) : redirect()->route('dashboard');
    }
}
