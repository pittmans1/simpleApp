<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    public function auditLogs(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return response()->json(AuditLog::withoutGlobalScope('tenant')->latest('created_at')->paginate(100));
    }

    public function users(Tenant $tenant): JsonResponse
    {
        $this->authorize('manage', $tenant);

        return response()->json($tenant->users()->select('users.id', 'users.name', 'users.email', 'users.created_at')->withPivot('role')->paginate(50));
    }

    public function updateUser(Request $request, Tenant $tenant, int $user, AuditLogService $audit): JsonResponse
    {
        $this->authorize('manage', $tenant);
        $role = $request->validate(['role' => ['required', 'in:owner,admin,member']])['role'];
        abort_unless($tenant->users()->whereKey($user)->exists(), 404);
        $tenant->users()->updateExistingPivot($user, ['role' => $role]);
        $audit->record('membership.updated', $tenant);

        return response()->json(['user_id' => $user, 'role' => $role]);
    }

    public function updateTenant(Request $request, Tenant $tenant, AuditLogService $audit): JsonResponse
    {
        $this->authorize('manage', $tenant);
        $tenant->update($request->validate(['name' => ['required', 'string', 'max:120'], 'settings' => ['nullable', 'array']]));
        $audit->record('tenant.updated', $tenant);

        return response()->json($tenant->fresh());
    }

    public function command(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('manage', $tenant);
        $command = $request->validate(['command' => ['required', 'string', 'in:about,route:list,queue:monitor']])['command'];
        $exitCode = Artisan::call($command, $command === 'queue:monitor' ? ['--max' => 1] : []);

        return response()->json(['command' => $command, 'exit_code' => $exitCode, 'output' => Artisan::output()]);
    }
}
