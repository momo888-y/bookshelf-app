<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_book(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'テスト駆動開発',
            'author' => 'Kent Beck',
            'isbn' => '9784274217883',
            'published_date' => '2017-10-14',
            'description' => 'TDDの解説書。',
            'image_url' => 'https://example.com/tdd.jpg',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->post('/books', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'title' => 'テスト駆動開発',
            'user_id' => $user->id,
        ]);
    }

    public function test_storing_book_attaches_genres(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'ジャンル紐付けテスト',
            'author' => 'テスト著者',
            'isbn' => '9781234567897',
            'published_date' => '2020-01-01',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $this->actingAs($user)->post('/books', $data);

        $book = Book::where('title', 'ジャンル紐付けテスト')->first();
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    public function test_store_book_validation_fails_with_empty_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/books', []);

        $response->assertSessionHasErrors(['title', 'author', 'isbn', 'published_date', 'genres']);
    }

    public function test_authenticated_user_can_update_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => $book->isbn,
            'published_date' => '2021-05-05',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put("/books/{$book->id}", $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);
    }

    public function test_updating_book_syncs_genres(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $oldGenre = Genre::factory()->create();
        $book->genres()->sync([$oldGenre->id]);
        $newGenres = Genre::factory()->count(2)->create();

        $data = [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => '2021-05-05',
            'genres' => $newGenres->pluck('id')->toArray(),
        ];

        $this->actingAs($user)->put("/books/{$book->id}", $data);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
        foreach ($newGenres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    public function test_authenticated_user_can_delete_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
