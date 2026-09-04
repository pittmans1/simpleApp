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
        $this->authorize('manage', app(TenantContext::class)->get());

        return response()->json(AuditLog::query()->latest('created_at')->paginate(100));
    }
}
