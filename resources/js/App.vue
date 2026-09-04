<template>
    <main class="dashboard-shell">
        <header class="dashboard-header"><div><p class="eyebrow">Trash Panda / {{ dashboardKind === 'tenant' ? tenantName : 'Personal studio' }}</p><h1>Good evening, {{ userName }}</h1><p class="dashboard-subtitle">{{ dashboardKind === 'tenant' ? 'Your team workspace at a glance.' : 'Your personal portfolio and activity at a glance.' }}</p></div><div class="header-actions"><span class="workspace-badge">{{ dashboardKind === 'tenant' ? 'Team workspace' : 'Personal workspace' }}</span><span class="stream-pill" :class="{ offline: !ops.streamConnected }"><i></i> {{ ops.streamConnected ? 'Live stream' : 'Reconnecting' }}</span><button class="icon-button" title="Toggle color theme" aria-label="Toggle color theme" @click="toggleTheme">{{ isDark ? '☼' : '◐' }}</button><form method="POST" action="/logout"><input type="hidden" name="_token" :value="csrf"><button class="logout-button" type="submit">Log out</button></form></div></header>
        <nav class="view-nav" aria-label="Dashboard views"><button :class="{ active: activeView === 'overview' }" @click="activeView = 'overview'">Overview</button><button :class="{ active: activeView === 'widgets' }" @click="activeView = 'widgets'; loadWidgets()">Widget lab</button><button :class="{ active: activeView === 'activity' }" @click="activeView = 'activity'; loadActivity()">Activity center</button><button :class="{ active: activeView === 'about' }" @click="activeView = 'about'">About & resume</button><button :class="{ active: activeView === 'admin' }" @click="activeView = 'admin'">Admin</button></nav>
        <section class="signal-grid" aria-label="Workspace health"><article class="signal-card signal-card--primary"><span class="card-label">Workspace health</span><strong>98.7<span>%</span></strong><small><b>+2.4%</b> vs last week</small><div class="health-line"><span style="width: 88%"></span></div></article><article class="signal-card"><span class="card-label">Active sessions</span><strong>1,284</strong><small><b>+18.2%</b> live right now</small><div class="mini-bars"><i v-for="height in [34, 43, 38, 61, 52, 72, 64, 86, 76]" :key="height" :style="{ height: `${height}%` }"></i></div></article><article class="signal-card"><span class="card-label">Event throughput</span><strong>24.8k<span>/m</span></strong><small><b>+8.1%</b> from yesterday</small><div class="sparkline"><span v-for="height in [35, 42, 30, 57, 45, 68, 59, 82, 72, 91]" :key="height" :style="{ height: `${height}%` }"></span></div></article></section>
        <section class="content-grid"><article class="panel market-panel"><div class="panel-heading"><div><span class="card-label">Market pulse</span><h2>Live feed <em>{{ ops.marketMood }}</em></h2></div><button class="text-button" @click="ops.tick">Refresh <span>↗</span></button></div><div class="quote-list"><div v-for="quote in ops.quotes" :key="quote.symbol" class="quote-row"><div class="quote-name"><span class="ticker">{{ quote.symbol.slice(0, 1) }}</span><div><strong>{{ quote.symbol }}</strong><small>{{ quote.name }}</small></div></div><div class="quote-chart"><span v-for="(point, index) in quote.points" :key="index" :style="{ height: `${point}%` }"></span></div><strong class="quote-price">${{ quote.price.toFixed(2) }}<small :class="{ negative: quote.change < 0 }">{{ quote.change > 0 ? '+' : '' }}{{ quote.change }}%</small></strong></div></div><footer class="panel-footer">Prices delayed by 15 minutes <span>Updated {{ updatedLabel }}</span></footer></article><article class="panel services-panel"><div class="panel-heading"><div><span class="card-label">Runtime observability</span><h2>Container health</h2></div><button class="dots-button" title="More container actions" aria-label="More container actions">•••</button></div><div class="service-list"><div v-for="service in ops.services" :key="service.name" class="service-row"><span class="service-dot" :class="service.color"></span><div class="service-copy"><strong>{{ service.name }}</strong><small>{{ service.latency }} latency</small></div><span class="service-status" :class="service.color">{{ service.status }}</span><div class="load-track"><span :class="service.color" :style="{ width: `${service.load}%` }"></span></div><small class="load-value">{{ service.load }}%</small></div></div><div class="container-footer"><span class="docker-mark">◇</span><span><strong>Docker compose</strong><small>3 containers running</small></span><span class="uptime">99.99% uptime</span></div></article></section>
        <market-chart v-if="activeView === 'overview'" :quotes="ops.quotes" :updated-label="updatedLabel"></market-chart><docker-health-widget v-if="activeView === 'overview'" :services="ops.services" :containers="ops.docker.containers" :source="ops.docker.source"></docker-health-widget>
        <section v-if="activeView === 'widgets'" class="panel management-panel"><div class="panel-heading"><div><span class="card-label">Admin workspace</span><h2>Widget configuration</h2></div><button class="text-button" @click="loadWidgets">Reload widgets ↗</button></div><div v-if="loading" class="loading-state">Loading your tenant configuration...</div><div v-else class="management-list"><div v-for="widget in widgets" :key="widget.id" class="management-row"><span><strong>{{ widget.title }}</strong><small>{{ widget.type }} · position {{ widget.position }}</small></span><span class="service-status mint">Configured</span></div><p v-if="!widgets.length" class="empty-state">No widgets configured yet. Your dashboard is using the demo layout.</p></div></section>
        <section v-if="activeView === 'activity'" class="activity-grid"><article class="panel"><div class="panel-heading"><div><span class="card-label">Inbox</span><h2>Notifications</h2></div></div><div v-if="!notifications.length" class="empty-state">No new notifications.</div><div v-for="notification in notifications" :key="notification.id" class="activity-row"><span class="activity-icon">!</span><span><strong>{{ notification.title || 'Workspace update' }}</strong><small>{{ notification.created_at }}</small></span></div></article><article class="panel"><div class="panel-heading"><div><span class="card-label">Trace</span><h2>Audit stream</h2></div></div><div v-if="!auditLogs.length" class="empty-state">No audit events available.</div><div v-for="log in auditLogs" :key="log.id" class="activity-row"><span class="activity-icon">↗</span><span><strong>{{ log.event }}</strong><small>{{ log.created_at }}</small></span></div></article></section>
        <about-panel v-if="activeView === 'about'"></about-panel><admin-panel v-if="activeView === 'admin'" :tenant-id="tenantId"></admin-panel><activity-log v-if="activeView === 'activity'" title="Interaction stream" eyebrow="Live site data" :items="auditLogs" @refresh="loadActivity"></activity-log><p class="demo-note">Demo telemetry is flowing locally <span>•</span> Echo is ready for Reverb events</p>
    </main>
</template>

<script>
import { mapStores } from 'pinia';
import { useOpsStore } from './store/ops';
import AboutPanel from './components/AboutPanel.vue';
import AdminPanel from './components/AdminPanel.vue';
import ActivityLog from './components/ActivityLog.vue';
import DockerHealthWidget from './components/DockerHealthWidget.vue';
import MarketChart from './components/MarketChart.vue';

export default {
    name: 'App',
    components: { AboutPanel, ActivityLog, AdminPanel, DockerHealthWidget, MarketChart },
    computed: {
        ...mapStores(useOpsStore),
        ops() {
            return this.opsStore;
        },
        isDark() {
            return document.documentElement.dataset.theme === 'dark';
        },
        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content;
        },
        userName() {
            return document.body.dataset.userName || 'operator';
        },
        tenantId() {
            return document.body.dataset.tenantId || '';
        },
        tenantName() {
            return document.body.dataset.tenantName || 'Personal studio';
        },
        dashboardKind() {
            return document.body.dataset.dashboardKind || 'personal';
        },
        updatedLabel() {
            return this.opsStore.lastUpdated.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
    },
    mounted() {
        this.opsStore.connectToStream();
        this.opsStore.fetchLiveData();
        this.refreshTimer = window.setInterval(() => this.opsStore.tick(), 4000);
        this.liveDataTimer = window.setInterval(() => this.opsStore.fetchLiveData(), 30000);
    },
    beforeUnmount() {
        window.clearInterval(this.refreshTimer);
        window.clearInterval(this.liveDataTimer);
    },
    data() {
        return {
            refreshTimer: null,
            liveDataTimer: null,
            activeView: 'overview',
            loading: false,
            widgets: [],
            notifications: [],
            auditLogs: [],
        };
    },
    methods: {
        toggleTheme() {
            document.documentElement.dataset.theme = this.isDark ? 'light' : 'dark';
        },
        async loadWidgets() {
            const tenantId = document.body.dataset.tenantId;
            if (!tenantId) return;
            this.loading = true;
            try {
                const response = await fetch(`/tenants/${tenantId}/dashboard/widgets`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                this.widgets = data.data || [];
            } finally {
                this.loading = false;
            }
        },
        async loadActivity() {
            const tenantId = document.body.dataset.tenantId;
            if (!tenantId) return;
            const headers = { Accept: 'application/json' };
            const [notifications, auditLogs] = await Promise.all([fetch(`/tenants/${tenantId}/notifications`, { headers }), fetch(`/tenants/${tenantId}/audit-logs`, { headers })]);
            this.notifications = (await notifications.json()).data || [];
            this.auditLogs = (await auditLogs.json()).data || [];
        },
    },
};
</script>