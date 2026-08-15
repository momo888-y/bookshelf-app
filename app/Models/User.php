<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // 登録した書籍（1対多の親 → hasMany）
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    // 投稿したレビュー（1対多の親 → hasMany）
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // お気に入りにした書籍（多対多）
    public function favoriteBooks()
    {
        return $this->belongsToMany(Book::class, 'favorites');
    }

    // いいねしたレビュー（多対多）
    public function likedReviews()
    {
        return $this->belongsToMany(Review::class, 'review_likes');
    }
}
