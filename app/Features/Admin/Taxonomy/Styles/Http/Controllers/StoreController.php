<?php

namespace App\Features\Admin\Taxonomy\Styles\Http\Controllers;

use App\Features\Admin\Taxonomy\Styles\Actions\CreateStyleAction;
use App\Features\Admin\Taxonomy\Styles\Http\Requests\StoreStyleRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class StoreController extends Controller
{
    public function __invoke(StoreStyleRequest $request, CreateStyleAction $action): RedirectResponse
    {
        $action->execute($request->validated());
        return redirect()->route('admin.styles.index')->with('status', 'Style created successfully.');
    }
}
