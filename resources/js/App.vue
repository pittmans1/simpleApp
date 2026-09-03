<template>
    <main class="app-shell">
        <p class="eyebrow">Laravel + Vue trash pandas</p>
        <h1>{{ appName }}</h1>
        <p class="lede">Your Vite-powered frontend is ready.</p>
    </main>
</template>
<script>
export default {
    name: 'App',
    data() {
        return {
            appName: import.meta.env.VITE_APP_NAME || 'Laravel',
        };
    },
};
</script>