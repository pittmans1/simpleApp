<?php

namespace App\Services;

use App\Models\TenantNotification;
use App\Models\User;
use App\Support\TenantContext;

class NotificationService
{
    public function send(User $user, string $type, string $title, string $body, array $data = []): TenantNotification
    {
        abort_unless($user->tenants()->whereKey(app(TenantContext::class)->id())->exists(), 403);

        return TenantNotification::create([
            'user_id' => $user->getKey(),
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
