<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_collect_each_achievement_once(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->withHeader('X-CSRF-TOKEN', 'test-token')
            ->postJson('/user/achievements', ['key' => 'trash-taste'])
            ->assertOk()
            ->assertJson(['achievements' => ['trash-taste']]);
        $this->actingAs($user)
            ->withSession(['_token' => 'test-token'])
            ->withHeader('X-CSRF-TOKEN', 'test-token')
            ->postJson('/user/achievements', ['key' => 'trash-taste'])
            ->assertOk()
            ->assertJson(['achievements' => ['trash-taste']]);

        $this->assertSame(['trash-taste'], $user->refresh()->achievements);
    }

    public function test_unknown_achievements_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['_token' => 'test-token'])
            ->withHeader('X-CSRF-TOKEN', 'test-token')
            ->postJson('/user/achievements', ['key' => 'not-a-panda'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['key']);
    }
}
