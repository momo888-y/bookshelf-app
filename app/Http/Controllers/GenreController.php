<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;

class GenreController extends Controller
{
    // 一覧（書籍数付き・本が多い順）
    public function index()
    {
        $genres = Genre::withCount('books')
            ->orderBy('books_count', 'desc')
            ->get();

        return view('genres.index', compact('genres'));
    }

    // 詳細（そのジャンルの書籍を登録日新しい順）
    public function show(Genre $genre)
    {
        $books = $genre->books()
            ->with('genres')
            ->orderBy('books.created_at', 'desc')
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    // 登録画面
    public function create()
    {
        return view('genres.create');
    }

    // 登録処理
    public function store(StoreGenreRequest $request)
    {
        Genre::create(['name' => $request->name]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを作成しました。');
    }

    // 編集画面
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    // 更新処理
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $genre->update(['name' => $request->name]);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを更新しました。');
    }

    // 削除処理（紐づく本があれば拒否）
    public function destroy(Genre $genre)
    {
        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')
                ->with('error', 'このジャンルには書籍が紐付いているため削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
