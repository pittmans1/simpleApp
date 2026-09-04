<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Demo') }}</title>
        @vite(['resources/js/app.js'])
    </head>
    <body @auth data-user-name="{{ auth()->user()->name }}" data-tenant-id="{{ auth()->user()->tenants->first()?->id }}" @endauth>
        <main class="home-shell">
            <h1>Welcome to Trash Panda</h1>
            @auth
                <p>Signed in as {{ auth()->user()->name }}.</p>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit">Log out</button>
                </form>
            @else
                <h2>Log in</h2>
                <form method="POST" action="/login" class="login-form">
                    @csrf
                    <label>Email <input type="email" name="email" required autocomplete="email"></label>
                    <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
                    <button type="submit">Log in</button>
                </form>

                <nav class="sso-links" aria-label="Single sign-on providers">
                    @foreach (['google' => 'Google', 'twitter' => 'X', 'linkedin' => 'LinkedIn'] as $provider => $label)
                        @if (config("services.{$provider}.client_id"))
                            <a href="{{ url("/auth/{$provider}/redirect") }}">Continue with {{ $label }}</a>
                        @endif
                    @endforeach
                </nav>
            @endauth
            <!-- <element-trash-panda></element-trash-panda> -->
        </main>
        <!-- <element-theme-selctor></element-theme-selctor> -->
    </body>
</html>
