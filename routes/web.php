<?php

use Illuminate\Support\Facades\Route;

// トップ（書籍一覧）… Chapter 6で本実装。今は仮。
Route::get('/', function () {
    return '書籍一覧（準備中）';
})->name('books.index');

// 認証が必要なページの動作確認用（Chapter 6で本実装に置き換え）
Route::middleware('auth')->group(function () {
    Route::get('/mypage', fn() => 'ログイン成功！認証ページです')->name('mypage');
});
