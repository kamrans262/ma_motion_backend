<?php

namespace App\Features\Admin\Taxonomy\Styles\Http\Controllers;

use App\Features\Admin\Taxonomy\Styles\Actions\ToggleStatusStyleAction;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class ToggleStatusController extends Controller
{
    public function __invoke(ArtworkStyle $style, ToggleStatusStyleAction $action): RedirectResponse
    {
        $action->execute($style);
        return back()->with('status', 'Style status updated successfully.');
    }
}
