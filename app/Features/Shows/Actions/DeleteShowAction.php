<?php

namespace App\Features\Shows\Actions;

use App\Features\Shows\Models\Show;

final class DeleteShowAction
{
    public function execute(Show $show): void
    {
        $show->delete();
    }
}
