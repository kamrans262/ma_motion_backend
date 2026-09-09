<?php

namespace App\Features\Admin\Taxonomy\Styles\Actions;

use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Features\Admin\Taxonomy\Support\TaxonomyData;

final class UpdateStyleAction
{
    public function execute(ArtworkStyle $item, array $data): ArtworkStyle
    {
        $item->update(TaxonomyData::normalized($data));
        return $item->refresh();
    }
}
