<?php

namespace App\Features\Content\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'slug', 'body', 'is_published', 'is_system', 'sort_order', 'published_at'])]
final class AppContentPage extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_system' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
