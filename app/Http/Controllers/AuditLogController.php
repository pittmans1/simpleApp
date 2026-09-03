<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()->tenants()->whereKey(app(TenantContext::class)->id())->first()?->pivot->role, ['owner', 'admin'], true), 403);

        return response()->json(AuditLog::query()->latest('created_at')->paginate(100));
    }
}
