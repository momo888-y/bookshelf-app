<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreBookRequest;
use App\Models\Book;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = \App\Models\Book::with('genres')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('books.index', compact('books'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = \App\Models\Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        // バリデーション済みのデータを取得
        $validated = $request->validated();

        // 登録者は現在ログイン中のユーザー
        $validated['user_id'] = auth()->id();

        // 書籍を作成
        $book = \App\Models\Book::create($validated);

        // ジャンルを紐付け（多対多）
        $book->genres()->sync($request->genres);

        // 登録した書籍の詳細画面へ
        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user', 'reviews.likedByUsers']);

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
    }
}
