<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'author_name',
        'body',
        'category',
        'user_id',
        'journey_id',
        'is_published',
    ];

    public function journey()
    {
        return $this->belongsTo(Journey::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
