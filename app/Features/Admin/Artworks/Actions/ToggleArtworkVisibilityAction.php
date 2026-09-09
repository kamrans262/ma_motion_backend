<?php

namespace App\Features\Admin\Artworks\Actions;

use App\Features\Artworks\Models\Artwork;

final class ToggleArtworkVisibilityAction
{
    public function execute(Artwork $artwork): Artwork
    {
        $artwork->forceFill(['is_visible' => ! $artwork->is_visible])->save();
        return $artwork->fresh(['maker.makerProfile', 'type', 'style', 'location', 'media']);
    }
}
