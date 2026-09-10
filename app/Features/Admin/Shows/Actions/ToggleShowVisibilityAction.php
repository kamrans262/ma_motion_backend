<?php

namespace App\Features\Admin\Shows\Actions;

use App\Features\Notifications\Services\SavedMakerShowNotificationService;
use App\Features\Shows\Models\Show;

final class ToggleShowVisibilityAction
{
    public function __construct(private readonly SavedMakerShowNotificationService $notifications) {}

    public function execute(Show $show): Show
    {
        $show->forceFill(['is_visible' => ! $show->is_visible])->save();
        $show = $show->fresh(['maker.makerProfile', 'location', 'artworks.primaryMedia']);

        if ($show->is_visible) {
            $this->notifications->announceIfEligible($show);
        }

        return $show;
    }
}
