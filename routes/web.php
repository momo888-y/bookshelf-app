<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

// トップ = 書籍一覧
Route::get('/', [BookController::class, 'index'])->name('books.index');

// 認証が必要なページの動作確認用
Route::middleware('auth')->group(function () {
    Route::get('/mypage', fn() => 'ログイン成功！認証ページです')->name('mypage');
});

// 書籍CRUD（indexは上で定義済みなので除外）
Route::resource('books', BookController::class)->except(['index']);

// 仮ルート（後で各機能を本実装したら置き換える）
Route::middleware('auth')->group(function () {
    Route::get('/ranking', fn() => 'ランキング（準備中）')->name('ranking.index');
    Route::get('/favorites', fn() => 'お気に入り（準備中）')->name('favorites.index');
    Route::get('/genres', fn() => 'ジャンル管理（準備中）')->name('genres.index');

    // 以下、後で本実装する仮ルート
    Route::post('/favorites/{book}/toggle', fn() => back())->name('favorites.toggle');
    Route::post('/books/{book}/reviews', fn() => back())->name('reviews.store');
    Route::post('/reviews/{review}/like', fn() => back())->name('reviews.like');
    Route::get('/reviews/{review}/edit', fn() => 'レビュー編集（準備中）')->name('reviews.edit');
    Route::put('/reviews/{review}', fn() => back())->name('reviews.update');
    Route::delete('/reviews/{review}', fn() => back())->name('reviews.destroy');
});
