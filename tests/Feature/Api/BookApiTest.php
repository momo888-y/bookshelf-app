<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_200_with_data_and_meta(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [['id', 'title', 'author', 'genres', 'average_rating', 'reviews_count']],
            'links',
            'meta',
        ]);
    }

    public function test_index_keyword_filters_by_title(): void
    {
        Book::factory()->create(['title' => 'ユニークタイトルXYZ']);
        Book::factory()->create(['title' => '別の本']);

        $response = $this->getJson('/api/v1/books?keyword=ユニークタイトルXYZ');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_index_keyword_filters_by_author(): void
    {
        Book::factory()->create(['author' => 'ユニーク著者ABC']);
        Book::factory()->create(['author' => '別の著者']);

        $response = $this->getJson('/api/v1/books?keyword=ユニーク著者ABC');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_index_filters_by_genre_id(): void
    {
        $genre = Genre::factory()->create();
        $matching = Book::factory()->create();
        $matching->genres()->attach($genre->id);
        Book::factory()->create();

        $response = $this->getJson("/api/v1/books?genre_id={$genre->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_index_default_per_page_is_20(): void
    {
        Book::factory()->count(25)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
    }

    public function test_index_returns_empty_array_when_no_match(): void
    {
        $response = $this->getJson('/api/v1/books?keyword=存在しないキーワード12345');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_invalid_genre_id_returns_422(): void
    {
        $response = $this->getJson('/api/v1/books?genre_id=99999');

        $response->assertStatus(422);
    }

    public function test_index_per_page_over_limit_returns_422(): void
    {
        $response = $this->getJson('/api/v1/books?per_page=101');

        $response->assertStatus(422);
    }

    public function test_average_rating_is_null_when_no_reviews(): void
    {
        Book::factory()->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.average_rating', null);
    }

    public function test_show_returns_200(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $book->id);
    }

    public function test_show_returns_404_for_missing_id(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }

    public function test_store_creates_book_and_returns_201(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $payload = [
            'user_id' => $user->id,
            'title' => 'API登録テスト',
            'author' => 'テスト著者',
            'isbn' => '9781111111117',
            'published_date' => '2023-01-01',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/v1/books', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', ['title' => 'API登録テスト']);
    }

    public function test_store_creates_book_genre_records(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $payload = [
            'user_id' => $user->id,
            'title' => 'ジャンル紐付けAPI',
            'author' => 'テスト著者',
            'isbn' => '9782222222227',
            'published_date' => '2023-01-01',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $this->postJson('/api/v1/books', $payload);

        $book = Book::where('title', 'ジャンル紐付けAPI')->first();
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    public function test_store_validation_error_returns_422(): void
    {
        $response = $this->postJson('/api/v1/books', []);

        $response->assertStatus(422);
    }

    public function test_update_returns_200(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(1)->create();

        $payload = [
            'title' => '更新後API',
            'author' => '更新著者',
            'isbn' => $book->isbn,
            'published_date' => '2023-05-05',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '更新後API']);
    }

    public function test_update_returns_404_for_missing_id(): void
    {
        $genres = Genre::factory()->count(1)->create();

        $payload = [
            'title' => 'x',
            'author' => 'x',
            'isbn' => '9783333333337',
            'published_date' => '2023-05-05',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->putJson('/api/v1/books/99999', $payload);

        $response->assertStatus(404);
    }

    public function test_destroy_returns_204(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_destroy_returns_404_for_missing_id(): void
    {
        $response = $this->deleteJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }
}
