<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_favorites_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertViewIs('favorites.index');
    }

    public function test_guest_is_redirected_from_favorites_index(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_toggle_favorite(): void
    {
        $book = Book::factory()->create();

        $response = $this->post("/favorites/{$book->id}/toggle");

        $response->assertRedirect('/login');
    }

    public function test_favorite_toggle_add_remove_readd(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post("/favorites/{$book->id}/toggle");
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user)->post("/favorites/{$book->id}/toggle");
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user)->post("/favorites/{$book->id}/toggle");
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_favorites_index_shows_only_own_favorites(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $myBook = Book::factory()->create();
        $othersBook = Book::factory()->create();

        $user->favoriteBooks()->attach($myBook->id);
        $other->favoriteBooks()->attach($othersBook->id);

        $response = $this->actingAs($user)->get('/favorites');

        $books = $response->viewData('books');
        $this->assertTrue($books->contains('id', $myBook->id));
        $this->assertFalse($books->contains('id', $othersBook->id));
    }
}
