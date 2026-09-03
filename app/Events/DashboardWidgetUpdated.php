<?php

namespace App\Events;

use App\Models\DashboardWidget;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardWidgetUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public DashboardWidget $widget, public string $action = 'updated') {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('tenant.'.$this->widget->tenant_id)];
    }

    public function broadcastAs(): string
    {
        return 'dashboard.widget.'.$this->action;
    }

    public function broadcastWith(): array
    {
        return ['widget' => $this->widget->toArray()];
    }
}
