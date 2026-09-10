<?php

namespace App\Features\Admin\Content\Http\Controllers;

use App\Features\Admin\Content\Actions\UpdateContentPageAction;
use App\Features\Admin\Content\Http\Requests\UpdateContentPageRequest;
use App\Features\Content\Models\AppContentPage;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class UpdateController extends Controller
{
    public function __invoke(UpdateContentPageRequest $request, AppContentPage $contentPage, UpdateContentPageAction $action): RedirectResponse
    {
        $action->execute($contentPage, $request->validated());

        return redirect()->route('admin.content.edit', $contentPage)->with('status', 'Content page updated successfully.');
    }
}
