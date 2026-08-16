<?php

namespace App\Http\Controllers;

use App\Models\Book;

class FavoriteController extends Controller
{
    // お気に入り一覧
    public function index()
    {
        $books = auth()->user()->favoriteBooks()
            ->with('genres')
            ->orderBy('favorites.created_at', 'desc')
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    // お気に入りトグル
    public function toggle(Book $book)
    {
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back();
    }
}
