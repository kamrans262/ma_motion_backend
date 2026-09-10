<?php

namespace App\Features\Admin\Content\Http\Controllers;

use App\Features\Admin\Content\Http\Requests\ContentIndexRequest;
use App\Features\Admin\Content\Services\ContentManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

final class IndexController extends Controller
{
    public function __invoke(ContentIndexRequest $request, ContentManagementService $content): View
    {
        $filters = $request->validated();

        return view('admin.content.index', [
            'pages' => $content->paginate($filters),
            'summary' => $content->summary(),
            'filters' => $filters,
        ]);
    }
}
