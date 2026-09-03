<?php

namespace App\Http\Controllers;

use App\Models\TenantNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(TenantNotification::query()
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->latest()
            ->paginate(50));
    }

    public function read(Request $request, TenantNotification $notification): JsonResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->getAuthIdentifier(), 404);
        $notification->update(['read_at' => now()]);

        return response()->json($notification->fresh());
    }
}
