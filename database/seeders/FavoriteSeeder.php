<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $bookIds = Book::pluck('id');

        foreach ($users as $user) {
            // 3〜5冊をランダムに選ぶ
            $favoriteCount = rand(3, 5);
            $favoriteBookIds = $bookIds->random($favoriteCount);

            // お気に入りに追加（既存は消さずに追加）
            $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
        }
    }
}
