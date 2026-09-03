<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_save_a_theme_preference(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/user/theme', ['theme' => 'trashpanda'])
            ->assertOk()
            ->assertJson(['theme' => 'trashpanda']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'theme' => 'trashpanda',
        ]);
    }

    public function test_invalid_theme_preferences_are_rejected(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/user/theme', ['theme' => 'neon'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['theme']);
    }
}
