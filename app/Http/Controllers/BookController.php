<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();

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
        $book = Book::create($validated);

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
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        // バリデーション済みデータで更新
        $book->update($request->validated());

        // ジャンルを紐付け直し（syncで置き換え）
        $book->genres()->sync($request->genres);

        // 書籍詳細画面へ
        return redirect()->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
