<?php

namespace App\Features\Admin\Content\Http\Controllers;

use App\Features\Admin\Content\Actions\DeleteContentPageAction;
use App\Features\Content\Models\AppContentPage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class DestroyController extends Controller
{
    public function __invoke(AppContentPage $contentPage, DeleteContentPageAction $action): RedirectResponse
    {
        $action->execute($contentPage);

        return redirect()->route('admin.content.index')->with('status', 'Content page deleted successfully.');
    }
}
