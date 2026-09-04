const formatBytes = (value) => {
    const bytes = Number(value) || 0;
    if (bytes === 0) return '0 B';

    const units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const size = 1024 ** index;
    const formatted = bytes / size;

    return `${formatted >= 10 || index === 0 ? formatted.toFixed(0) : formatted.toFixed(1)} ${units[index]}`;
};

export function normalizeServices(containers = [], services = []) {
    const source = Array.isArray(containers) && containers.length
        ? containers
        : Array.isArray(services) && services.length
            ? services
            : [];

    return source.map((container) => {
        const state = String(container.state || container.status || 'unknown');
        const isRunning = state.toLowerCase() === 'running';
        const name = container.name || 'container';
        const cpuPercent = Number(container.cpu_percent ?? container.cpu ?? 0);
        const memoryPercent = Number(container.memory_percent ?? container.memory ?? 0);
        const memoryUsage = Number(container.memory_usage ?? 0);
        const memoryLimit = Number(container.memory_limit ?? 0);
        const pids = Number(container.pids ?? 0);
        const load = Math.min(100, Math.max(0, Math.round(Math.max(memoryPercent, cpuPercent, isRunning ? 68 : 0))));

        return {
            name,
            status: container.state || 'unknown',
            state: container.state || 'unknown',
            latency: container.status || 'offline',
            load,
            color: isRunning ? 'mint' : 'amber',
            cpu: Number.isFinite(cpuPercent) ? cpuPercent : 0,
            memory: Number.isFinite(memoryPercent) ? memoryPercent : 0,
            memoryUsage: formatBytes(memoryUsage),
            memoryLimit: formatBytes(memoryLimit),
            pids,
            netIo: formatBytes(container.net_io ?? 0),
            blockIo: formatBytes(container.block_io ?? 0),
            healthy: isRunning,
        };
    });
}

export function getHealthPercent(services = []) {
    if (!Array.isArray(services) || !services.length) {
        return 0;
    }

    const healthyCount = services.filter((service) => service.color === 'mint' || service.healthy || service.status === 'running').length;
    return Math.round((healthyCount / services.length) * 100);
}
