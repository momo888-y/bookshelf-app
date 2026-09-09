<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_like_review(): void
    {
        $review = Review::factory()->create();

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');
    }

    public function test_like_toggle_add_remove_readd(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // 1回目：いいね追加
        $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 2回目：いいね解除
        $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 3回目：再いいね
        $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_user_can_like_own_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_duplicate_like_is_prevented(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // 1回いいねした後、DBには1件だけ
        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $count = \DB::table('review_likes')
            ->where('user_id', $user->id)
            ->where('review_id', $review->id)
            ->count();

        $this->assertEquals(1, $count);
    }
}
