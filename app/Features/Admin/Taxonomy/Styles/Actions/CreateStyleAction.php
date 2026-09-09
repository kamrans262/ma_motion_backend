<?php

namespace App\Features\Admin\Taxonomy\Styles\Actions;

use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Admin\Taxonomy\Support\TaxonomyData;

final class CreateStyleAction
{
    public function execute(array $data): ArtworkStyle
    {
        return ArtworkStyle::create(TaxonomyData::normalized($data));
    }
}
