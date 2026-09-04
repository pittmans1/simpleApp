<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class DockerMetricsService
{
    /**
     * @return array{source: string, containers: array<int, array<string, mixed>>, updated_at: string}
     */
    public function snapshot(): array
    {
        $socket = config('services.docker.socket');
        $host = config('services.docker.host');

        if (! $socket && ! $host) {
            return ['source' => 'unavailable', 'containers' => [], 'updated_at' => now()->toIso8601String()];
        }

        if ($socket && ! file_exists($socket) && ! is_link($socket)) {
            if (! $host) {
                return ['source' => 'unavailable', 'containers' => [], 'updated_at' => now()->toIso8601String()];
            }
        }

        try {
            $http = Http::connectTimeout(2)->timeout(5);

            if ($socket && (file_exists($socket) || is_link($socket))) {
                $http = $http->withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => $socket]]);
            }

            $containers = $http
                ->get($this->dockerBaseUri('/containers/json', $host), ['all' => false])
                ->throw()
                ->json();

            if (! is_array($containers)) {
                return ['source' => 'error', 'containers' => [], 'updated_at' => now()->toIso8601String()];
            }

            $normalized = collect($containers)->map(function (array $container): array {
                $id = $container['Id'] ?? null;
                $stats = $this->statsForContainer($id);

                $names = $container['Names'] ?? [];
                $rawName = is_array($names) ? ($names[0] ?? 'unknown') : $names;
                $nameValue = is_array($rawName) ? ($rawName['Name'] ?? 'unknown') : $rawName;

                return [
                    'id' => substr((string) ($id ?? ''), 0, 12),
                    'name' => ltrim((string) ($nameValue ?? 'unknown'), '/'),
                    'image' => $container['Image'] ?? 'unknown',
                    'state' => $container['State'] ?? 'unknown',
                    'status' => $container['Status'] ?? 'unknown',
                    'cpu_percent' => $stats['cpu_percent'] ?? 0,
                    'memory_usage' => $stats['memory_usage'] ?? 0,
                    'memory_limit' => $stats['memory_limit'] ?? 0,
                    'memory_percent' => $stats['memory_percent'] ?? 0,
                    'net_io' => $stats['net_io'] ?? 0,
                    'block_io' => $stats['block_io'] ?? 0,
                    'pids' => $stats['pids'] ?? 0,
                    'healthy' => strtolower((string) ($container['State'] ?? '')) === 'running',
                    'created' => $container['Created'] ?? null,
                ];
            })->values()->all();

            return [
                'source' => 'docker-engine',
                'containers' => $normalized,
                'updated_at' => now()->toIso8601String(),
            ];
        } catch (Throwable) {
            return ['source' => 'error', 'containers' => [], 'updated_at' => now()->toIso8601String()];
        }
    }

    /**
     * @return array{cpu_percent: float, memory_usage: int, memory_limit: int, memory_percent: float, net_io: int, block_io: int, pids: int}
     */
    protected function statsForContainer(?string $containerId): array
    {
        if (! $containerId) {
            return ['cpu_percent' => 0, 'memory_usage' => 0, 'memory_limit' => 0, 'memory_percent' => 0, 'net_io' => 0, 'block_io' => 0, 'pids' => 0];
        }

        try {
            $socket = config('services.docker.socket');
            $host = config('services.docker.host');
            $request = Http::connectTimeout(2)->timeout(5);

            if ($socket && (file_exists($socket) || is_link($socket))) {
                $request = $request->withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => $socket]]);
            }

            $response = $request->get($this->dockerBaseUri("/containers/{$containerId}/stats?stream=false", $host));

            $data = $response->json();

            if (! is_array($data)) {
                return ['cpu_percent' => 0, 'memory_usage' => 0, 'memory_limit' => 0, 'memory_percent' => 0, 'net_io' => 0, 'block_io' => 0, 'pids' => 0];
            }

            $memoryUsage = (int) ($data['memory_stats']['usage'] ?? 0);
            $memoryLimit = (int) ($data['memory_stats']['limit'] ?? 0);
            $cpuDelta = ((int) ($data['cpu_stats']['cpu_usage']['total_usage'] ?? 0) - (int) ($data['precpu_stats']['cpu_usage']['total_usage'] ?? 0));
            $systemDelta = ((int) ($data['cpu_stats']['system_cpu_usage'] ?? 0) - (int) ($data['precpu_stats']['system_cpu_usage'] ?? 0));
            $cpuPercent = $systemDelta > 0 ? (($cpuDelta / $systemDelta) * 100) * (count($data['cpu_stats']['cpu_usage']['percpu_usage'] ?? []) ?: 1) : 0;

            return [
                'cpu_percent' => round(max(0, min(100, $cpuPercent)), 2),
                'memory_usage' => $memoryUsage,
                'memory_limit' => $memoryLimit,
                'memory_percent' => $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0,
                'net_io' => ((int) ($data['networks']['eth0']['rx_bytes'] ?? 0) + (int) ($data['networks']['eth0']['tx_bytes'] ?? 0)) ?: 0,
                'block_io' => ((int) ($data['blkio_stats']['io_service_bytes_recursive'][0]['value'] ?? 0) + (int) ($data['blkio_stats']['io_service_bytes_recursive'][1]['value'] ?? 0)) ?: 0,
                'pids' => (int) ($data['pids_stats']['current'] ?? 0),
            ];
        } catch (Throwable) {
            return ['cpu_percent' => 0, 'memory_usage' => 0, 'memory_limit' => 0, 'memory_percent' => 0, 'net_io' => 0, 'block_io' => 0, 'pids' => 0];
        }
    }

    protected function dockerBaseUri(string $path, ?string $host = null): string
    {
        if ($host) {
            return rtrim($host, '/') . $path;
        }

        return 'http://localhost' . $path;
    }
}
