<?php

namespace App\Features\Admin\Taxonomy\Types\Actions;

use App\Features\Taxonomy\Models\ArtworkType;
use App\Features\Admin\Taxonomy\Support\TaxonomyData;

final class UpdateTypeAction
{
    public function execute(ArtworkType $item, array $data): ArtworkType
    {
        $item->update(TaxonomyData::normalized($data));
        return $item->refresh();
    }
}
