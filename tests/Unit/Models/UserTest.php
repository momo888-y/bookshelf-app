<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->books);
        $this->assertInstanceOf(Book::class, $user->books->first());
    }

    public function test_user_has_many_reviews(): void
    {
        $user = User::factory()->create();
        Review::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->reviews);
        $this->assertInstanceOf(Review::class, $user->reviews->first());
    }

    public function test_user_has_many_favorite_books(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(3)->create();
        $user->favoriteBooks()->sync($books->pluck('id'));

        $this->assertCount(3, $user->favoriteBooks);
        $this->assertInstanceOf(Book::class, $user->favoriteBooks->first());
    }

    public function test_user_has_many_liked_reviews(): void
    {
        $user = User::factory()->create();
        $reviews = Review::factory()->count(2)->create();
        $user->likedReviews()->sync($reviews->pluck('id'));

        $this->assertCount(2, $user->likedReviews);
        $this->assertInstanceOf(Review::class, $user->likedReviews->first());
    }

    public function test_deleting_user_cascades_books(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
