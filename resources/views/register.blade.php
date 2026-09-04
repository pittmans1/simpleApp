@include('app')
@auth
<h2>Register</h2>
<form method="POST" action="/register" class="login-form">
    @csrf
    <label>Name <input type="text" name="name" required autocomplete="name"></label>
    <label>Email <input type="email" name="email" required autocomplete="email"></label>
    <label>Workspace <input type="text" name="tenant_name" required autocomplete="organization"></label>
    <label>Password <input type="password" name="password" required minlength="12" autocomplete="new-password"></label>
    <label>Confirm password <input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label>
    <button type="submit">Create account</button>
</form>
@endauth