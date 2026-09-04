<template>
    <section class="panel docker-widget"><div class="panel-heading"><div><span class="card-label">Runtime observability</span><h2>Container health</h2></div><span class="source-badge" :class="{ live: source === 'docker-engine' }">{{ source === 'docker-engine' ? 'Live engine' : 'Demo mode' }}</span></div><div class="docker-overview"><div class="docker-ring" :style="{ '--ring-progress': `${health}%` }"><strong>{{ health }}%</strong><small>healthy</small></div><div><strong>{{ containers.length || services.length }} active services</strong><p>Real-time state from your Docker network.</p></div></div><div class="service-list"><div v-for="service in displayedServices" :key="service.name" class="service-row"><span class="service-dot" :class="service.color"></span><div class="service-copy"><strong>{{ service.name }}</strong><small>{{ service.status }} · {{ service.latency }}</small></div><div class="load-track"><span :class="service.color" :style="{ width: `${service.load}%` }"></span></div><small class="load-value">{{ service.load }}%</small></div></div></section>
</template>
<script>
export default {
    name: 'DockerHealthWidget',
    props: { services: { type: Array, default: () => [] }, containers: { type: Array, default: () => [] }, source: { type: String, default: 'demo' } },
    data() { return { liveContainers: [], liveSource: this.source }; },
    mounted() { this.loadContainers(); },
    computed: {
        displayedServices() { const containers = this.containers.length ? this.containers : this.liveContainers; return containers.length ? containers.map((container) => ({ name: container.name, status: container.state, latency: container.status, load: container.state === 'running' ? 68 : 0, color: container.state === 'running' ? 'mint' : 'amber' })) : this.services; },
        health() { return this.displayedServices.length ? Math.round(this.displayedServices.filter((service) => service.color === 'mint').length / this.displayedServices.length * 100) : 0; },
    },
    methods: {
        async loadContainers() {
            try {
                const response = await fetch('/operations/docker', { headers: { Accept: 'application/json' } });
                const data = await response.json();
                this.liveContainers = data.containers || [];
                this.liveSource = data.source || this.source;
            } catch (error) {
                this.liveContainers = [];
            }
        },
    },
};
</script>
