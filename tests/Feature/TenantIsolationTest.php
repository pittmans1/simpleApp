<?php

namespace Tests\Feature;

use App\Models\DashboardWidget;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_cannot_read_another_tenants_widgets(): void
    {
        $user = User::factory()->create();
        $allowed = Tenant::create(['name' => 'Microsoft', 'slug' => 'microsoft']);
        $blocked = Tenant::create(['name' => 'Other Corp', 'slug' => 'other-corp']);
        $allowed->users()->attach($user, ['role' => 'member']);

        app(TenantContext::class)->set($allowed);
        DashboardWidget::create(['key' => 'allowed', 'title' => 'Allowed', 'type' => 'metric']);
        app(TenantContext::class)->set($blocked);
        DashboardWidget::create(['key' => 'blocked', 'title' => 'Blocked', 'type' => 'metric']);

        $this->actingAs($user)->getJson('/tenants/microsoft/dashboard/widgets')
            ->assertOk()
            ->assertJsonFragment(['key' => 'allowed'])
            ->assertJsonMissing(['key' => 'blocked']);

        $this->actingAs($user)->getJson('/tenants/other-corp/dashboard/widgets')->assertNotFound();
    }

    public function test_members_cannot_change_dashboard_widgets(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['name' => 'Microsoft', 'slug' => 'microsoft']);
        $tenant->users()->attach($user, ['role' => 'member']);

        $this->actingAs($user)->postJson('/tenants/microsoft/dashboard/widgets', [
            'key' => 'sales',
            'title' => 'Sales',
            'type' => 'metric',
        ])->assertForbidden();
    }

    public function test_private_channel_is_only_authorized_for_tenant_members(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['name' => 'Microsoft', 'slug' => 'microsoft']);
        $otherTenant = Tenant::create(['name' => 'Other Corp', 'slug' => 'other-corp']);
        $tenant->users()->attach($user, ['role' => 'member']);

        $channels = Broadcast::getChannels();
        $authorize = $channels->get('tenant.{tenantId}');

        $this->assertTrue($authorize($user, $tenant->id));
        $this->assertFalse($authorize($user, $otherTenant->id));
    }
}
