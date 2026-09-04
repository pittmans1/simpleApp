<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\DockerMetricsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
	public function index(Request $request, DockerMetricsService $docker): View
	{
		$snapshot = $docker->snapshot();
		return view('dashboard', [
			'tenant' => $request->user()->tenants()->first(),
			'isTenantDashboard' => false,
			'dockerContainers' => $snapshot['containers'] ?? [],
			'dockerSource' => $snapshot['source'] ?? 'demo',
			'dockerUpdatedAt' => $snapshot['updated_at'] ?? now()->toIso8601String(),
		]);
	}

	public function tenant(Tenant $tenant, DockerMetricsService $docker): View
	{
		$snapshot = $docker->snapshot();

		return view('dashboard', [
			'tenant' => $tenant,
			'isTenantDashboard' => true,
			'dockerContainers' => $snapshot['containers'] ?? [],
			'dockerSource' => $snapshot['source'] ?? 'demo',
			'dockerUpdatedAt' => $snapshot['updated_at'] ?? now()->toIso8601String(),
		]);
	}
}
