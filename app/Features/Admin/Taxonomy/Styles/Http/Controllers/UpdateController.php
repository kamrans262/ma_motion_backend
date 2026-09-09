<?php

namespace App\Features\Admin\Taxonomy\Styles\Http\Controllers;

use App\Features\Admin\Taxonomy\Styles\Actions\UpdateStyleAction;
use App\Features\Admin\Taxonomy\Styles\Http\Requests\UpdateStyleRequest;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class UpdateController extends Controller
{
    public function __invoke(UpdateStyleRequest $request, ArtworkStyle $style, UpdateStyleAction $action): RedirectResponse
    {
        $action->execute($style, $request->validated());
        return redirect()->route('admin.styles.edit', $style)->with('status', 'Style updated successfully.');
    }
}
