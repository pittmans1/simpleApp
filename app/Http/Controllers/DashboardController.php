<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
	public function index(Request $request): View
	{
		return view('dashboard', [
			'tenant' => $request->user()->tenants()->first(),
			'isTenantDashboard' => false,
		]);
	}

	public function tenant(Tenant $tenant): View
	{
		return view('dashboard', [
			'tenant' => $tenant,
			'isTenantDashboard' => true,
		]);
	}
}
