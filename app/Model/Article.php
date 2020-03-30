<?php

declare(strict_types=1);

namespace App\Model;

class Article extends Model
{
    protected $appends = [
        'poster_full',
    ];

    protected $casts = [
        'can_show' => 'bool',
        'is_index' => 'bool',
    ];

    protected $fillable = [
        'title', 'subtitle', 'poster', 'can_show', 'is_index', 'sort', 'markdown', 'content',
    ];

    protected $table = 'articles';

    public function getPosterFullAttribute()
    {
        if (array_key_exists('poster', $this->attributes)) {
            return config('app_url') . $this->poster;
        }
        return '';
    }
}


