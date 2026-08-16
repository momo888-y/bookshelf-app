<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Review;

class ReviewController extends Controller
{
    // レビュー投稿
    public function store(StoreReviewRequest $request, Book $book)
    {
        $review = $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを投稿しました。');
    }
    // レビュー編集画面
    public function edit(Review $review)
    {
        return view('reviews.edit', compact('review'));
    }

    // レビュー更新
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました。');
    }

    // レビュー削除
    public function destroy(Review $review)
    {
        $book = $review->book;
        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }

    // いいねトグル
    public function like(Review $review)
    {
        // 自分のレビューにはいいねできない
        if ($review->user_id === auth()->id()) {
            return back();
        }

        auth()->user()->likedReviews()->toggle($review->id);

        return back();
    }
}
