<?php

namespace App\Features\Admin\Content\Http\Controllers;

use App\Features\Admin\Content\Actions\ToggleContentPagePublishAction;
use App\Features\Content\Models\AppContentPage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class TogglePublishController extends Controller
{
    public function __invoke(AppContentPage $contentPage, ToggleContentPagePublishAction $action): RedirectResponse
    {
        $action->execute($contentPage);

        return back()->with('status', $contentPage->fresh()->is_published ? 'Content page published.' : 'Content page moved to draft.');
    }
}
