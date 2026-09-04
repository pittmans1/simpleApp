<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="nonce" content="{{ Vite::cspNonce() }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageTitle ?? 'Trash Panda' }}</title>
        @vite(['resources/js/app.js'])
    </head>
    <body
        @auth data-user-name="{{ auth()->user()->name }}" @endauth
        @if (isset($isTenantDashboard))
            data-dashboard="true"
            data-dashboard-kind="{{ $isTenantDashboard ? 'tenant' : 'personal' }}"
            data-tenant-id="{{ $tenant?->slug }}"
            data-tenant-name="{{ $tenant?->name }}"
        @endif
    >
