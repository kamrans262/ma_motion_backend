<?php

namespace App\Features\Admin\Taxonomy\Styles\Actions;

use App\Features\Taxonomy\Models\ArtworkStyle;

final class ToggleStatusStyleAction
{
    public function execute(ArtworkStyle $item): ArtworkStyle
    {
        $item->update(['is_active' => ! $item->is_active]);
        return $item->refresh();
    }
}
