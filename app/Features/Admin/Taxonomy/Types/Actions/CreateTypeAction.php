<?php

namespace App\Features\Admin\Taxonomy\Types\Actions;

use App\Features\Taxonomy\Models\ArtworkType;
use App\Features\Admin\Taxonomy\Support\TaxonomyData;

final class CreateTypeAction
{
    public function execute(array $data): ArtworkType
    {
        return ArtworkType::create(TaxonomyData::normalized($data));
    }
}
