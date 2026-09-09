<?php

namespace App\Features\Admin\Taxonomy\Styles\Http\Controllers;

use App\Features\Admin\Taxonomy\Styles\Http\Requests\StyleIndexRequest;
use App\Features\Admin\Taxonomy\Styles\Services\StyleManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(StyleIndexRequest $request, StyleManagementService $service): View
    {
        $filters = $request->validated();

        return view('admin.taxonomy.index', [
            'items' => $service->paginate($filters),
            'filters' => $filters,
            'pageTitle' => 'Styles',
            'singular' => 'Style',
            'routePrefix' => 'admin.styles',
            'description' => 'Manage the visual styles used by Maker workflows, discovery and filters.',
        ]);
    }
}
