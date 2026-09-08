<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_ranking(): void
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
        $response->assertViewIs('ranking.index');
    }

    public function test_books_are_ordered_by_average_rating_desc(): void
    {
        $low = Book::factory()->create();
        Review::factory()->create(['book_id' => $low->id, 'rating' => 2]);

        $high = Book::factory()->create();
        Review::factory()->create(['book_id' => $high->id, 'rating' => 5]);

        $mid = Book::factory()->create();
        Review::factory()->create(['book_id' => $mid->id, 'rating' => 3]);

        $response = $this->get('/ranking');

        $ranked = $response->viewData('rankedBooks');
        $this->assertEquals(
            [$high->id, $mid->id, $low->id],
            $ranked->pluck('id')->toArray()
        );
    }

    public function test_books_without_reviews_are_excluded(): void
    {
        $reviewed = Book::factory()->create();
        Review::factory()->create(['book_id' => $reviewed->id, 'rating' => 4]);

        $noReview = Book::factory()->create();

        $response = $this->get('/ranking');

        $ranked = $response->viewData('rankedBooks');
        $this->assertTrue($ranked->contains('id', $reviewed->id));
        $this->assertFalse($ranked->contains('id', $noReview->id));
    }

    public function test_ranking_shows_at_most_ten_books(): void
    {
        $books = Book::factory()->count(12)->create();
        foreach ($books as $book) {
            Review::factory()->create(['book_id' => $book->id, 'rating' => 4]);
        }

        $response = $this->get('/ranking');

        $ranked = $response->viewData('rankedBooks');
        $this->assertCount(10, $ranked);
    }
}
