<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    public function test_book_belongs_to_many_genres(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(3)->create();
        $book->genres()->sync($genres->pluck('id'));

        $this->assertCount(3, $book->genres);
        $this->assertInstanceOf(Genre::class, $book->genres->first());
    }

    public function test_book_has_many_reviews(): void
    {
        $book = Book::factory()->create();
        Review::factory()->count(2)->create(['book_id' => $book->id]);

        $this->assertCount(2, $book->reviews);
        $this->assertInstanceOf(Review::class, $book->reviews->first());
    }

    public function test_book_has_many_favorited_by_users(): void
    {
        $book = Book::factory()->create();
        $users = User::factory()->count(3)->create();
        $book->favoritedBy()->sync($users->pluck('id'));

        $this->assertCount(3, $book->favoritedBy);
        $this->assertInstanceOf(User::class, $book->favoritedBy->first());
    }

    public function test_deleting_book_cascades_related_data(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->sync([$genre->id]);
        $user = User::factory()->create();
        $book->favoritedBy()->sync([$user->id]);
        $review = Review::factory()->create(['book_id' => $book->id]);

        $book->delete();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertDatabaseMissing('book_genre', ['book_id' => $book->id]);
        $this->assertDatabaseMissing('favorites', ['book_id' => $book->id]);
    }
}
