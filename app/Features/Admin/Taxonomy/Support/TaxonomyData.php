<?php

namespace App\Features\Admin\Taxonomy\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class TaxonomyData
{
    /** @param array{name:string,is_active:mixed,sort_order:int} $data */
    public static function normalized(array $data): array
    {
        $name = trim($data['name']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => (bool) $data['is_active'],
            'sort_order' => (int) $data['sort_order'],
        ];
    }

    /** @param class-string<Model> $modelClass */
    public static function slugExists(string $modelClass, string $name, ?int $ignoreId = null): bool
    {
        $query = $modelClass::withTrashed()->where('slug', Str::slug($name));

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
