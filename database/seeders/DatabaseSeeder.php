<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\DashboardWidget;
use App\Models\TenantNotification;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(['email' => $account['email']], [
                'name' => $account['name'],
                'password' => Hash::make('ChangeMe-'.str()->random(24)),
            ]);
            $tenant = Tenant::updateOrCreate(['slug' => $account['slug']], ['name' => $account['tenant']]);
            $tenant->users()->syncWithoutDetaching([$user->id => ['role' => $account['role']]]);
            app(TenantContext::class)->set($tenant);

            foreach ([
                ['key' => 'workspace-health', 'title' => 'Workspace health', 'type' => 'metric', 'position' => 1],
                ['key' => 'market-pulse', 'title' => 'Market pulse', 'type' => 'market', 'position' => 2],
                ['key' => 'container-health', 'title' => 'Container health', 'type' => 'runtime', 'position' => 3],
            ] as $widget) {
                DashboardWidget::updateOrCreate(['tenant_id' => $tenant->id, 'key' => $widget['key']], $widget);
            }

            TenantNotification::firstOrCreate(['tenant_id' => $tenant->id, 'user_id' => $user->id, 'type' => 'system', 'title' => 'Demo workspace ready'], ['body' => 'Your live operations dashboard is ready to explore.', 'data' => ['priority' => 'normal']]);
        }

        User::updateOrCreate(['email' => env('ADMIN_EMAIL', 'pittman.a.scott@gmail.com')], [
            'name' => 'Scott Pittman',
            'password' => Hash::make('Password-example'),
            'role' => 'admin',
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
