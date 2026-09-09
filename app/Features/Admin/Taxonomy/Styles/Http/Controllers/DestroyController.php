<?php

namespace App\Features\Admin\Taxonomy\Styles\Http\Controllers;

use App\Features\Admin\Taxonomy\Styles\Actions\DeleteStyleAction;
use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class DestroyController extends Controller
{
    public function __invoke(ArtworkStyle $style, DeleteStyleAction $action): RedirectResponse
    {
        $action->execute($style);
        return redirect()->route('admin.styles.index')->with('status', 'Style deleted safely.');
    }
}
