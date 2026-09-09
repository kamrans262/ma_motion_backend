<?php

namespace App\Features\Admin\Taxonomy\Types\Http\Controllers;

use App\Features\Admin\Taxonomy\Types\Actions\UpdateTypeAction;
use App\Features\Admin\Taxonomy\Types\Http\Requests\UpdateTypeRequest;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class UpdateController extends Controller
{
    public function __invoke(UpdateTypeRequest $request, ArtworkType $type, UpdateTypeAction $action): RedirectResponse
    {
        $action->execute($type, $request->validated());
        return redirect()->route('admin.types.edit', $type)->with('status', 'Type updated successfully.');
    }
}
