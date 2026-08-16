<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\RankingController;

// トップ = 書籍一覧
Route::get('/', [BookController::class, 'index'])->name('books.index');

// ランキング（公開ページ・ゲスト可）
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// 認証が必要なページの動作確認用
Route::middleware('auth')->group(function () {
    Route::get('/mypage', fn() => 'ログイン成功！認証ページです')->name('mypage');
});

// 書籍CRUD（indexは上で定義済みなので除外）
Route::resource('books', BookController::class)->except(['index']);

// 仮ルート（後で各機能を本実装したら置き換える）
Route::middleware('auth')->group(function () {
    Route::get('/favorites', [\App\Http\Controllers\FavoriteController::class, 'index'])->name('favorites.index');
    Route::resource('genres', \App\Http\Controllers\GenreController::class);

    // 以下、後で本実装する仮ルート
    Route::post('/favorites/{book}/toggle', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/books/{book}/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/like', [\App\Http\Controllers\ReviewController::class, 'like'])->name('reviews.like');
    Route::get('/reviews/{review}/edit', [\App\Http\Controllers\ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\ReviewController::class, 'destroy'])->name('reviews.destroy');
});
