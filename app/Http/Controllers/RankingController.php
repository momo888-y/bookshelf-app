<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    /**
     * レビュー平均評価TOP10を表示する（公開ページ）。
     */
    public function index()
    {
        $rankedBooks = Book::withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->having('reviews_count', '>', 0)
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
