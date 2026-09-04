<template>
    <section class="panel chart-panel">
        <div class="panel-heading"><div><span class="card-label">Market intelligence</span><h2>{{ selectedSymbol }} <em>{{ timeframe }}</em></h2></div><div class="chart-actions"><button v-for="option in timeframes" :key="option" :class="{ active: timeframe === option }" type="button" @click="setTimeframe(option)">{{ option }}</button></div></div>
        <div class="chart-toolbar"><button v-for="quote in availableQuotes" :key="quote.symbol" :class="{ selected: quote.symbol === selectedSymbol }" type="button" @click="selectSymbol(quote.symbol)">{{ quote.symbol }}</button><span class="chart-updated">{{ updatedLabel }} <i></i></span></div>
        <div class="chart-canvas-wrap"><canvas ref="canvas"></canvas></div>
        <div class="chart-summary"><span>Last price <strong>${{ selectedQuote.price.toFixed(2) }}</strong></span><span>Change <strong :class="{ negative: selectedQuote.change < 0 }">{{ selectedQuote.change > 0 ? '+' : '' }}{{ selectedQuote.change }}%</strong></span><span>Signal <strong>{{ signal }}</strong></span></div>
    </section>
</template>
<script>
import Chart from 'chart.js/auto';

export default {
    name: 'MarketChart',
    props: { quotes: { type: Array, default: () => [] }, updatedLabel: { type: String, default: '' } },
    data() { return { chart: null, liveQuotes: [], selectedSymbol: 'NVDA', timeframe: '1D', timeframes: ['1D', '1W', '1M', '1Y'] }; },
    computed: {
        availableQuotes() { return this.quotes.length ? this.quotes : this.liveQuotes; },
        selectedQuote() { return this.availableQuotes.find((quote) => quote.symbol === this.selectedSymbol) || { price: 0, change: 0, points: [] }; },
        signal() { return this.selectedQuote.change >= 0 ? 'Accumulating' : 'Watching'; },
    },
    mounted() { this.loadQuotes(); this.renderChart(); },
    beforeUnmount() { this.chart?.destroy(); },
    watch: { quotes: { deep: true, handler() { this.renderChart(); } } },
    methods: {
        selectSymbol(symbol) { this.selectedSymbol = symbol; this.renderChart(); },
        setTimeframe(timeframe) { this.timeframe = timeframe; this.renderChart(); },
        async loadQuotes() {
            try {
                const response = await fetch('/operations/stocks?symbols=NVDA,AAPL,TSLA', { headers: { Accept: 'application/json' } });
                const data = await response.json();
                this.liveQuotes = (data.quotes || []).map((quote) => ({ ...quote, name: quote.symbol, points: [35, 42, 30, 57, 45, 68, 59, 82, 72, 91] }));
                this.renderChart();
            } catch (error) {
                this.liveQuotes = [];
            }
        },
        renderChart() {
            if (!this.$refs.canvas) return;
            this.chart?.destroy();
            const context = this.$refs.canvas.getContext('2d');
            this.chart = new Chart(context, { type: 'line', data: { labels: this.selectedQuote.points.map((_, index) => index + 1), datasets: [{ data: this.selectedQuote.points, borderColor: '#3aab7d', backgroundColor: 'rgb(58 171 125 / 12%)', fill: true, tension: .42, pointRadius: 0, borderWidth: 2 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { displayColors: false } }, scales: { x: { display: false }, y: { display: false } } } });
        },
    },
};
</script>
