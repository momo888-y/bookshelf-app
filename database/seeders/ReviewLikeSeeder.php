<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = Review::all();

        foreach ($reviews as $review) {
            // このレビューの投稿者以外のユーザーを候補にする（自分のレビューを除く）
            $candidates = User::where('id', '!=', $review->user_id)->get();

            // 0〜3人がいいね
            $likeCount = rand(0, 3);

            if ($likeCount > 0) {
                $likers = $candidates->random(min($likeCount, $candidates->count()));
                $review->likedBy()->syncWithoutDetaching($likers->pluck('id'));
            }
        }
    }
}
