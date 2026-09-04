import { computed, ref } from 'vue';
import { defineStore } from 'pinia';

const baseQuotes = [
    { symbol: 'NVDA', name: 'NVIDIA', price: 142.61, change: 3.42, points: [58, 54, 57, 50, 46, 49, 42, 44, 36, 39, 31, 28] },
    { symbol: 'AAPL', name: 'Apple', price: 229.98, change: 1.18, points: [42, 47, 45, 49, 46, 52, 51, 56, 53, 59, 58, 63] },
    { symbol: 'TSLA', name: 'Tesla', price: 351.42, change: -1.74, points: [65, 61, 63, 57, 59, 52, 55, 48, 51, 43, 46, 39] },
];

export const useOpsStore = defineStore('ops', () => {
    const quotes = ref(baseQuotes);
    const lastUpdated = ref(new Date());
    const streamConnected = ref(true);
    const services = ref([
        { name: 'api-gateway', status: 'Healthy', latency: '42ms', load: 38, color: 'mint' },
        { name: 'queue-worker', status: 'Healthy', latency: '18ms', load: 64, color: 'mint' },
        { name: 'reverb', status: 'Degraded', latency: '210ms', load: 81, color: 'amber' },
    ]);
    const docker = ref({ source: 'demo', containers: [] });
    const marketMood = computed(() => quotes.value.filter((quote) => quote.change > 0).length > 1 ? 'Risk on' : 'Mixed tape');

    function tick() {
        quotes.value = quotes.value.map((quote) => {
            const delta = (Math.random() - 0.44) * 0.7;
            return { ...quote, price: Number((quote.price + delta).toFixed(2)), change: Number((quote.change + delta / 2).toFixed(2)), points: [...quote.points.slice(1), Math.max(20, Math.min(78, quote.points.at(-1) + delta * 8))] };
        });
        lastUpdated.value = new Date();
    }

    function connectToStream() {
        const tenantId = document.body.dataset.tenantId;

        if (window.Echo && tenantId) {
            const channel = window.Echo.private(`tenant.${tenantId}`);
            ['created', 'updated', 'deleted'].forEach((action) => {
                channel.listen(`.dashboard.widget.${action}`, () => tick());
            });
        }
    }

    async function fetchLiveData() {
        try {
            const [stockResponse, dockerResponse] = await Promise.all([
                fetch('/operations/stocks?symbols=NVDA,AAPL,TSLA', { headers: { Accept: 'application/json' } }),
                fetch('/operations/docker', { headers: { Accept: 'application/json' } }),
            ]);
            const stockData = await stockResponse.json();
            const dockerData = await dockerResponse.json();
            if (stockData.quotes?.length) {
                quotes.value = quotes.value.map((quote) => ({ ...quote, ...(stockData.quotes.find((liveQuote) => liveQuote.symbol === quote.symbol) || {}) }));
            }
            docker.value = dockerData;
            if (dockerData.containers?.length) {
                services.value = dockerData.containers.slice(0, 3).map((container) => ({ name: container.name, status: container.state, latency: 'live', load: 0, color: container.state === 'running' ? 'mint' : 'amber' }));
            }
        } catch (error) {
            streamConnected.value = false;
        }
    }

    return { quotes, lastUpdated, streamConnected, services, docker, marketMood, tick, connectToStream, fetchLiveData };
});
