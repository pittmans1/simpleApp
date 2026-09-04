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
        [$socket, $host] = $this->resolveDockerConnection();

        try {
            $http = $this->httpClient($socket);

            $containers = $http
                ->get($this->dockerBaseUri('/containers/json', $host), ['all' => false])
                ->throw()
                ->json();

            if (! is_array($containers)) {
                return $this->emptyResult('error');
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
                    'cpu_percent' => $stats['cpu_percent'],
                    'memory_usage' => $stats['memory_usage'],
                    'memory_limit' => $stats['memory_limit'],
                    'memory_percent' => $stats['memory_percent'],
                    'net_io' => $stats['net_io'],
                    'block_io' => $stats['block_io'],
                    'pids' => $stats['pids'],
                    'healthy' => strtolower((string) ($container['State'] ?? '')) === 'running',
                    'created' => $container['Created'] ?? null,
                ];
            })->values()->all();

            return [
                'source' => $socket ? 'docker-socket' : 'docker-host',
                'containers' => $normalized,
                'updated_at' => now()->toIso8601String(),
            ];
        } catch (Throwable) {
            return $this->emptyResult('error');
        }
    }

    /**
     * @return array{cpu_percent: float, memory_usage: int, memory_limit: int, memory_percent: float, net_io: int, block_io: int, pids: int}
     */
    protected function statsForContainer(?string $containerId): array
    {
        if (! $containerId) {
            return $this->emptyStats();
        }

        try {
            [$socket, $host] = $this->resolveDockerConnection();
            $http = $this->httpClient($socket);

            $data = $http
                ->get($this->dockerBaseUri("/containers/{$containerId}/stats?stream=false", $host))
                ->json();

            if (! is_array($data)) {
                return $this->emptyStats();
            }

            $memoryUsage = (int) ($data['memory_stats']['usage'] ?? 0);
            $memoryLimit = (int) ($data['memory_stats']['limit'] ?? 0);

            $cpuDelta = ((int) ($data['cpu_stats']['cpu_usage']['total_usage'] ?? 0)
                - (int) ($data['precpu_stats']['cpu_usage']['total_usage'] ?? 0));

            $systemDelta = ((int) ($data['cpu_stats']['system_cpu_usage'] ?? 0)
                - (int) ($data['precpu_stats']['system_cpu_usage'] ?? 0));

            $cpuPercent = $systemDelta > 0
                ? (($cpuDelta / $systemDelta) * 100) * (count($data['cpu_stats']['cpu_usage']['percpu_usage'] ?? []) ?: 1)
                : 0;

            return [
                'cpu_percent' => round(max(0, min(100, $cpuPercent)), 2),
                'memory_usage' => $memoryUsage,
                'memory_limit' => $memoryLimit,
                'memory_percent' => $memoryLimit > 0 ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0,
                'net_io' => ((int) ($data['networks']['eth0']['rx_bytes'] ?? 0)
                    + (int) ($data['networks']['eth0']['tx_bytes'] ?? 0)),
                'block_io' => ((int) ($data['blkio_stats']['io_service_bytes_recursive'][0]['value'] ?? 0)
                    + (int) ($data['blkio_stats']['io_service_bytes_recursive'][1]['value'] ?? 0)),
                'pids' => (int) ($data['pids_stats']['current'] ?? 0),
            ];
        } catch (Throwable) {
            return $this->emptyStats();
        }
    }

    protected function httpClient(?string $socket): \Illuminate\Http\Client\PendingRequest
    {
        $http = Http::connectTimeout(2)->timeout(5);

        if ($socket && file_exists($socket)) {
            return $http->withOptions([
                'curl' => [CURLOPT_UNIX_SOCKET_PATH => $socket],
            ]);
        }

        return $http;
    }

    protected function dockerBaseUri(string $path, ?string $host = null): string
    {
        if ($host) {
            return rtrim($host, '/') . $path;
        }

        return 'http://localhost' . $path;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    protected function resolveDockerConnection(): array
    {
        $socket = config('services.docker.socket');
        $host = config('services.docker.host');

        // Normalize unix://host → socket path
        if (is_string($host) && str_starts_with($host, 'unix://')) {
            $socket = parse_url($host, PHP_URL_PATH) ?: substr($host, 7);
            $host = null;
        }

        // If socket doesn't exist, fallback to host
        if ($socket && ! file_exists($socket)) {
            $socket = null;
        }

        return [$socket, $host];
    }

    protected function emptyResult(string $source): array
    {
        return [
            'source' => $source,
            'containers' => [],
            'updated_at' => now()->toIso8601String(),
        ];
    }

    protected function emptyStats(): array
    {
        return [
            'cpu_percent' => 0,
            'memory_usage' => 0,
            'memory_limit' => 0,
            'memory_percent' => 0,
            'net_io' => 0,
            'block_io' => 0,
            'pids' => 0,
        ];
    }
}
