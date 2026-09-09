<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_genre_index(): void
    {
        $user = User::factory()->create();
        Genre::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/genres');

        $response->assertStatus(200);
        $response->assertViewIs('genres.index');
    }

    public function test_authenticated_user_can_access_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/genres/create');

        $response->assertStatus(200);
        $response->assertViewIs('genres.create');
    }

    public function test_authenticated_user_can_store_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', ['name' => '技術書']);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['name' => '技術書']);
    }

    public function test_store_genre_validation_fails_when_name_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', ['name' => '']);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_genre_validation_fails_when_name_is_duplicate(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => '小説']);

        $response = $this->actingAs($user)->post('/genres', ['name' => '小説']);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_authenticated_user_can_update_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '旧名']);

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", ['name' => '新名']);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '新名']);
    }

    public function test_genre_show_displays_related_book_titles(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['title' => 'ジャンルに紐づく本']);
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->get("/genres/{$genre->id}");

        $response->assertStatus(200);
        $response->assertViewIs('genres.show');
        $response->assertSee('ジャンルに紐づく本');
    }

    public function test_genre_without_books_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }
}
