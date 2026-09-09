<?php

namespace App\Features\Admin\Taxonomy\Styles\Http\Controllers;

use App\Features\Taxonomy\Models\ArtworkStyle;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class EditController extends Controller
{
    public function __invoke(ArtworkStyle $style): View
    {
        return view('admin.taxonomy.edit', [
            'item' => $style,
            'pageTitle' => 'Edit Style',
            'singular' => 'Style',
            'routePrefix' => 'admin.styles',
        ]);
    }
}
