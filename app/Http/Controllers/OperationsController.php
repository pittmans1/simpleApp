<?php

namespace App\Http\Controllers;

use App\Services\DockerMetricsService;
use App\Services\StockMarketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    public function docker(Request $request, DockerMetricsService $docker): JsonResponse
    {
        abort_unless($request->user(), 401);

        return response()->json($docker->snapshot());
    }

    public function stocks(Request $request, StockMarketService $stocks): JsonResponse
    {
        abort_unless($request->user(), 401);
        $symbols = $request->filled('symbols') ? $request->string('symbols')->explode(',')->all() : ['NVDA', 'AAPL', 'TSLA'];

        return response()->json($stocks->quotes($symbols));
    }
}
