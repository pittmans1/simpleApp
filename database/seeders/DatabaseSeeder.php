<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Microsoft Admin', 'email' => 'microsoft-admin@example.test', 'tenant' => 'Microsoft', 'slug' => 'microsoft', 'role' => 'owner'],
            ['name' => 'Northwind Admin', 'email' => 'northwind-admin@example.test', 'tenant' => 'Northwind Labs', 'slug' => 'northwind-labs', 'role' => 'owner'],
            ['name' => 'Acme Admin', 'email' => 'acme-admin@example.test', 'tenant' => 'Acme Research', 'slug' => 'acme-research', 'role' => 'owner'],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(['email' => $account['email']], [
                'name' => $account['name'],
                'password' => Hash::make('ChangeMe-'.str()->random(24)),
            ]);
            $tenant = Tenant::updateOrCreate(['slug' => $account['slug']], ['name' => $account['tenant']]);
            $tenant->users()->syncWithoutDetaching([$user->id => ['role' => $account['role']]]);
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
