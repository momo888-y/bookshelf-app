<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_post_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 4,
            'comment' => 'とても良い本でした。',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => 'とても良い本でした。',
        ]);
    }

    public function test_review_validation_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 6,
            'comment' => '',
        ]);

        $response->assertSessionHasErrors(['rating', 'comment']);
    }

    public function test_guest_cannot_post_review(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/books/{$book->id}/reviews", [
            'rating' => 4,
            'comment' => 'ゲストの投稿。',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_user_can_post_multiple_reviews_to_same_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 3,
            'comment' => '1回目のレビュー。',
        ]);
        $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => '2回目のレビュー。',
        ]);

        $this->assertEquals(2, Review::where('book_id', $book->id)->where('user_id', $user->id)->count());
    }

    public function test_owner_can_update_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'rating' => 2,
            'comment' => '更新後のコメント。',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 2,
            'comment' => '更新後のコメント。',
        ]);
    }

    public function test_non_owner_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->put("/reviews/{$review->id}", [
            'rating' => 1,
            'comment' => '他人による更新。',
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_non_owner_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete("/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_deleting_review_cascades_likes(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);
        $liker = User::factory()->create();
        $review->likedByUsers()->attach($liker->id);

        $this->actingAs($user)->delete("/reviews/{$review->id}");

        $this->assertDatabaseMissing('review_likes', ['review_id' => $review->id]);
    }
}
