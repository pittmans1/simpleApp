<template>
    <section class="panel docker-widget">
        <div class="panel-heading">
            <div>
                <span class="card-label">Runtime observability</span>
                <h2>Container health</h2>
            </div>
            <span class="source-badge" :class="{ live: effectiveSource === 'docker-engine' }">{{ effectiveSource === 'docker-engine' ? 'Live engine' : 'Demo mode' }}</span>
        </div>
        <div class="docker-overview">
            <div class="docker-ring" :style="{ '--ring-progress': `${health}%` }">
                <strong>{{ health }}%</strong>
                <small>healthy</small>
            </div>
            <div>
                <strong>{{ displayedServices.length }} active services</strong>
                <p>Real-time state from your Docker network.</p>
            </div>
        </div>
        <div class="service-list">
            <div v-for="service in displayedServices" :key="service.name" class="service-row">
                <span class="service-dot" :class="service.color"></span>
                <div class="service-meta">
                    <div class="service-copy">
                        <strong>{{ service.name }}</strong>
                        <small>{{ service.state }} · {{ service.latency }}</small>
                    </div>
                    <div class="service-metrics">
                        <span>CPU {{ Number(service.cpu || 0).toFixed(1) }}%</span>
                        <span>RAM {{ Number(service.memory || 0).toFixed(1) }}%</span>
                    </div>
                </div>
                <div class="load-track">
                    <span :class="service.color" :style="{ width: `${service.load}%` }"></span>
                </div>
                <div class="service-stats">
                    <span>{{ service.memoryUsage }}</span>
                    <span>{{ service.pids }} pids</span>
                    <span>{{ service.netIo }}/{{ service.blockIo }}</span>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
import { normalizeServices, getHealthPercent } from './dockerWidgetLogic.js';

export default {
    name: 'DockerHealthWidget',
    props: {
        services: { type: Array, default: () => [] },
        containers: { type: Array, default: () => [] },
        dockerContainers: { type: Array, default: null },
        source: { type: String, default: 'demo' },
        dockerSource: { type: String, default: null },
    },
    data() { return { liveContainers: [], liveSource: this.source || this.dockerSource || 'demo' }; },
    mounted() { this.loadContainers(); },
    computed: {
        effectiveSource() {
            return this.liveSource || this.dockerSource || this.source || 'demo';
        },
        resolvedContainers() {
            if (Array.isArray(this.dockerContainers) && this.dockerContainers.length) {
                return this.dockerContainers;
            }

            if (Array.isArray(this.containers) && this.containers.length) {
                return this.containers;
            }

            return this.liveContainers;
        },
        displayedServices() {
            return normalizeServices(this.resolvedContainers, this.services);
        },
        health() {
            return getHealthPercent(this.displayedServices);
        },
    },
    methods: {
        async loadContainers() {
            try {
                const response = await fetch('/operations/docker', { headers: { Accept: 'application/json' } });
                const data = await response.json();
                this.liveContainers = Array.isArray(data.containers) ? data.containers : [];
                this.liveSource = data.source || this.source || this.dockerSource || 'demo';
            } catch (error) {
                this.liveContainers = [];
                this.liveSource = this.source || this.dockerSource || 'demo';
            }
        },
    },
};
</script>
