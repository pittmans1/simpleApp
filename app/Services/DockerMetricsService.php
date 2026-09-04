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

        if (! $socket || ! file_exists($socket)) {
            return ['source' => 'unavailable', 'containers' => [], 'updated_at' => now()->toIso8601String()];
        }

        try {
            $containers = Http::withOptions(['curl' => [CURLOPT_UNIX_SOCKET_PATH => $socket]])
                ->connectTimeout(1)
                ->timeout(3)
                ->get('http://localhost/containers/json', ['all' => false])
                ->throw()
                ->json();

            return [
                'source' => 'docker-engine',
                'containers' => collect($containers)->map(fn (array $container): array => [
                    'id' => substr($container['Id'] ?? '', 0, 12),
                    'name' => ltrim($container['Names'][0] ?? 'unknown', '/'),
                    'image' => $container['Image'] ?? 'unknown',
                    'state' => $container['State'] ?? 'unknown',
                    'status' => $container['Status'] ?? 'unknown',
                ])->values()->all(),
                'updated_at' => now()->toIso8601String(),
            ];
        } catch (Throwable) {
            return ['source' => 'error', 'containers' => [], 'updated_at' => now()->toIso8601String()];
        }
    }
}
