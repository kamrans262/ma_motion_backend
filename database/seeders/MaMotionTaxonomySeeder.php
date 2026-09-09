<?php

namespace Database\Seeders;

use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Taxonomy\Models\ArtworkType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class MaMotionTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Painting', 'Sculpture', 'Furniture', 'Fashion', 'Architecture', 'Product Design',
            'Graphic Design', 'Photography', 'Textile', 'Ceramics', 'Installation', 'Digital Art',
            'Animation', 'Mural', 'Film', 'Lighting', 'Illustration',
        ];

        $styles = [
            'Contemporary', 'Traditional', 'Minimal', 'Modernist', 'Post-Modern', 'Brutalist',
            'Abstract', 'Conceptual', 'Industrial', 'Organic', 'Geometric', 'Experimental',
        ];

        $this->seedItems(ArtworkType::class, $types);
        $this->seedItems(ArtworkStyle::class, $styles);
    }

    /** @param class-string<ArtworkType|ArtworkStyle> $modelClass @param list<string> $names */
    private function seedItems(string $modelClass, array $names): void
    {
        foreach ($names as $index => $name) {
            $modelClass::withTrashed()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => ($index + 1) * 10],
            );
        }
    }
}
