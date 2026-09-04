@include('app', ['pageTitle' => $isTenantDashboard && $tenant ? $tenant->name.' · Trash Panda' : 'Personal dashboard · Trash Panda'])
<main class="dashboard-shell">
        <p class="eyebrow">Trash Panda / {{ $isTenantDashboard ? $tenant->name : 'Personal studio' }}</p>
        <h1>Good evening, {{ auth()->user()->name }}</h1>
        <nav class="view-nav" aria-label="Dashboard views">
            <a href="{{ $isTenantDashboard ? route('dashboard') : route('dashboard') }}">Overview</a>
            @if ($isTenantDashboard)
                <a href="#widgets">Widget lab</a>
                <a href="#activity">Activity center</a>
                <a href="#admin">Admin</a>
            @endif
            <a href="#about">About & resume</a>
        </nav>
        <market-chart></market-chart>
        <docker-health-widget
            :containers="{{ json_encode($dockerContainers ?? []) }}"
            :source="{{ json_encode($dockerSource ?? 'demo') }}"
            :docker-containers="{{ json_encode($dockerContainers ?? []) }}"
            :docker-source="{{ json_encode($dockerSource ?? 'demo') }}">
        </docker-health-widget>
        @if ($isTenantDashboard)
            <admin-panel tenant-id="{{ $tenant->slug }}"></admin-panel>
        @endif
        <about-panel></about-panel>
</main>
@include('layouts.footer')
