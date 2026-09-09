<?php

namespace App\Features\Admin\Taxonomy\Types\Http\Controllers;

use App\Features\Admin\Taxonomy\Types\Actions\ToggleStatusTypeAction;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class ToggleStatusController extends Controller
{
    public function __invoke(ArtworkType $type, ToggleStatusTypeAction $action): RedirectResponse
    {
        $action->execute($type);
        return back()->with('status', 'Type status updated successfully.');
    }
}
