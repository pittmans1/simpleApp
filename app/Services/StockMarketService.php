<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class StockMarketService
{
    /**
     * @param array<int, string> $symbols
     * @return array{source: string, quotes: array<int, array<string, mixed>>, updated_at: string}
     */
    public function quotes(array $symbols): array
    {
        $symbols = array_values(array_filter(array_map(fn (string $symbol): string => strtoupper(trim($symbol)), $symbols)));
        $key = config('services.stocks.key');
        $url = config('services.stocks.url');

        if ($key && $url) {
            try {
                $quotes = collect($symbols)->map(function (string $symbol) use ($key, $url): array {
                    $response = Http::connectTimeout(2)->timeout(4)->retry([100, 300], 1)->get($url, ['symbol' => $symbol, 'token' => $key])->throw()->json();
                    return ['symbol' => $symbol, 'price' => (float) ($response['c'] ?? 0), 'change' => (float) ($response['dp'] ?? 0)];
                })->all();

                return ['source' => 'provider', 'quotes' => $quotes, 'updated_at' => now()->toIso8601String()];
            } catch (Throwable) {
                // Keep the dashboard useful during provider outages.
            }
        }

        return ['source' => 'demo-fallback', 'quotes' => collect($symbols)->map(fn (string $symbol): array => ['symbol' => $symbol, 'price' => 100 + (crc32($symbol) % 300), 'change' => (crc32($symbol) % 700) / 100 - 3.5])->all(), 'updated_at' => now()->toIso8601String()];
    }
}
