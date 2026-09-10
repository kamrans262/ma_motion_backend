<?php

namespace App\Features\Admin\Content\Http\Controllers;

use App\Features\Admin\Content\Actions\CreateContentPageAction;
use App\Features\Admin\Content\Http\Requests\StoreContentPageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class StoreController extends Controller
{
    public function __invoke(StoreContentPageRequest $request, CreateContentPageAction $action): RedirectResponse
    {
        $page = $action->execute($request->validated());

        return redirect()->route('admin.content.edit', $page)->with('status', 'Content page created successfully.');
    }
}
