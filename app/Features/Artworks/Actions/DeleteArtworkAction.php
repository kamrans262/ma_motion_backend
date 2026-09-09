<?php

namespace App\Features\Artworks\Actions;

use App\Features\Artworks\Models\Artwork;

final class DeleteArtworkAction
{
    public function execute(Artwork $artwork): void
    {
        $artwork->delete();
    }
}
