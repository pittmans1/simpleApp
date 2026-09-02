<script setup>
const appName = import.meta.env.VITE_APP_NAME || 'Demo';
</script>

<template>
    <main class="app-shell">
        <p class="eyebrow">Laravel + Vue</p>
        <h1>{{ appName }}</h1>
        <p class="lede">Your Vite-powered frontend is ready.</p>
    </main>
</template>
