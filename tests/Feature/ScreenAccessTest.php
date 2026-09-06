<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_book_index(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('books.index');
    }

    public function test_guest_can_access_ranking(): void
    {
        $response = $this->get('/ranking');

        $response->assertStatus(200);
        $response->assertViewIs('ranking.index');
    }

    public function test_guest_can_access_book_detail(): void
    {
        $book = Book::factory()->create();

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertViewIs('books.show');
    }
}
