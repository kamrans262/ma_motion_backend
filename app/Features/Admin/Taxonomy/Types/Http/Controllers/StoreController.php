<?php

namespace App\Features\Admin\Taxonomy\Types\Http\Controllers;

use App\Features\Admin\Taxonomy\Types\Actions\CreateTypeAction;
use App\Features\Admin\Taxonomy\Types\Http\Requests\StoreTypeRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class StoreController extends Controller
{
    public function __invoke(StoreTypeRequest $request, CreateTypeAction $action): RedirectResponse
    {
        $action->execute($request->validated());
        return redirect()->route('admin.types.index')->with('status', 'Type created successfully.');
    }
}
