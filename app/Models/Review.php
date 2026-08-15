<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    // このレビューの投稿者（belongsTo）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // このレビューが対象とする書籍（belongsTo）
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // このレビューにいいねしたユーザー（多対多）
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'review_likes');
    }
}
