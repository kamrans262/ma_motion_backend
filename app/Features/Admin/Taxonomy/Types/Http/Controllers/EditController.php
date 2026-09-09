<?php

namespace App\Features\Admin\Taxonomy\Types\Http\Controllers;

use App\Features\Taxonomy\Models\ArtworkType;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class EditController extends Controller
{
    public function __invoke(ArtworkType $type): View
    {
        return view('admin.taxonomy.edit', [
            'item' => $type,
            'pageTitle' => 'Edit Type',
            'singular' => 'Type',
            'routePrefix' => 'admin.types',
        ]);
    }
}
