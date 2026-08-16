<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
    ];

    // このBookを登録したユーザー（1対多の子 → belongsTo）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // このBookが属するジャンル（多対多 → belongsToMany）
    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    // このBookに付いたレビュー（1対多の親 → hasMany）
    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    // このBookをお気に入りにしたユーザー（多対多 → belongsToMany）
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}
