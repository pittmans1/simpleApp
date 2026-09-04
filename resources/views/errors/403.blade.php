@include('app', ['pageTitle' => 'Access denied · Trash Panda'])
<main class="home-shell error-page">
    <p class="eyebrow">Trash Panda / Restricted area</p>
    <h1>403</h1>
    <h2>This space is not unlocked for you.</h2>
    <p>You are signed in, but your account does not have permission to access this admin action.</p>
    <p><a href="{{ route('dashboard') }}">Return to your dashboard</a></p>
</main>
@include('layouts.footer')