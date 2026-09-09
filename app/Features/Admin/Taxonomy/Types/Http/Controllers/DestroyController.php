<?php

namespace App\Features\Admin\Taxonomy\Types\Http\Controllers;

use App\Features\Admin\Taxonomy\Types\Actions\DeleteTypeAction;
use App\Features\Taxonomy\Models\ArtworkType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class DestroyController extends Controller
{
    public function __invoke(ArtworkType $type, DeleteTypeAction $action): RedirectResponse
    {
        $action->execute($type);
        return redirect()->route('admin.types.index')->with('status', 'Type deleted safely.');
    }
}
