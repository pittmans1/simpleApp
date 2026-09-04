<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function __construct(private Request $request)
    {
    }

    public function record(string $event, Model $auditable, array $metadata = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $this->request->user()?->getAuthIdentifier(),
            'event' => $event,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
