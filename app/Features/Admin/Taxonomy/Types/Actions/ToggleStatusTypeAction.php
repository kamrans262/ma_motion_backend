<?php

namespace App\Features\Admin\Taxonomy\Types\Actions;

use App\Features\Taxonomy\Models\ArtworkType;

final class ToggleStatusTypeAction
{
    public function execute(ArtworkType $item): ArtworkType
    {
        $item->update(['is_active' => ! $item->is_active]);
        return $item->refresh();
    }
}
