<?php

namespace App\Features\Admin\Taxonomy\Types\Http\Controllers;

use App\Features\Admin\Taxonomy\Types\Http\Requests\TypeIndexRequest;
use App\Features\Admin\Taxonomy\Types\Services\TypeManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(TypeIndexRequest $request, TypeManagementService $service): View
    {
        $filters = $request->validated();

        return view('admin.taxonomy.index', [
            'items' => $service->paginate($filters),
            'filters' => $filters,
            'pageTitle' => 'Types',
            'singular' => 'Type',
            'routePrefix' => 'admin.types',
            'description' => 'Manage the artwork categories used by Maker workflows, discovery and filters.',
        ]);
    }
}
