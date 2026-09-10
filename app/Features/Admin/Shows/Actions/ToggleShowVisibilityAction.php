<?php

namespace App\Features\Admin\Shows\Actions;

use App\Features\Shows\Models\Show;

final class ToggleShowVisibilityAction
{
    public function execute(Show $show): Show
    {
        $show->forceFill(['is_visible' => ! $show->is_visible])->save();

        return $show->fresh(['maker.makerProfile', 'location', 'artworks.primaryMedia']);
    }
}
