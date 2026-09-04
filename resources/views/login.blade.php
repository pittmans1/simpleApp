@include('app', ['pageTitle' => 'Sign in · Trash Panda'])
<main class="home-shell">
    @guest
        <p class="eyebrow">Trash Panda / Personal studio</p>
        <h1>Welcome back</h1>
        <h2>Log in</h2>
        @if ($errors->any())
            <div class="auth-error" role="alert">{{ $errors->first() }}</div>
        @endif
        @if (session('status'))
            <div class="auth-status" role="status">{{ session('status') }}</div>
        @endif
        <form method="POST" action="/login" class="login-form">
            @csrf
            <label>Email <input type="email" name="email" required autocomplete="email"></label>
            <label>Password <input type="password" name="password" required autocomplete="current-password"></label>
            <button type="submit">Log in</button>
        </form>
        <h2>Create an account</h2>
        <form method="POST" action="/register" class="login-form">
            @csrf
            <label>Name <input type="text" name="name" required autocomplete="name"></label>
            <label>Email <input type="email" name="email" required autocomplete="email"></label>
            <label>Workspace <input type="text" name="tenant_name" required autocomplete="organization"></label>
            <label>Password <input type="password" name="password" required minlength="12" autocomplete="new-password"></label>
            <label>Confirm password <input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label>
            <button type="submit">Create account</button>
        </form>
        <nav class="sso-links" aria-label="Single sign-on providers">
            @foreach (['google' => 'Google', 'twitter' => 'X', 'linkedin' => 'LinkedIn'] as $provider => $label)
                @if (config("services.{$provider}.client_id"))
                    <a href="{{ url("/auth/{$provider}/redirect") }}">Continue with {{ $label }}</a>
                @endif
            @endforeach
        </nav>
    @endguest
</main>
<element-theme-selctor></element-theme-selctor>
@include('layouts.footer')
