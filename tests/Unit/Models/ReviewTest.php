<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    public function test_review_belongs_to_book(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    public function test_review_has_many_liked_by_users(): void
    {
        $review = Review::factory()->create();
        $users = User::factory()->count(3)->create();
        $review->likedByUsers()->sync($users->pluck('id'));

        $this->assertCount(3, $review->likedByUsers);
        $this->assertInstanceOf(User::class, $review->likedByUsers->first());
    }

    public function test_deleting_review_cascades_likes(): void
    {
        $review = Review::factory()->create();
        $user = User::factory()->create();
        $review->likedByUsers()->sync([$user->id]);

        $review->delete();

        $this->assertDatabaseMissing('review_likes', ['review_id' => $review->id]);
    }
}
