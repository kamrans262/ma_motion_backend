<?php

namespace App\Features\Admin\Taxonomy\Types\Actions;

use App\Features\Taxonomy\Models\ArtworkType;

final class DeleteTypeAction
{
    public function execute(ArtworkType $item): void
    {
        $item->delete();
    }
}
