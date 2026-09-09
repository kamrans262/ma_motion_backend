<?php

namespace App\Features\Admin\Taxonomy\Styles\Actions;

use App\Features\Taxonomy\Models\ArtworkStyle;

final class DeleteStyleAction
{
    public function execute(ArtworkStyle $item): void
    {
        $item->delete();
    }
}
