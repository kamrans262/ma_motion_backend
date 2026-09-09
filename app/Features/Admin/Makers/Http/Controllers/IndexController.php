<?php

namespace App\Features\Admin\Makers\Http\Controllers;

use App\Features\Admin\Makers\Http\Requests\MakerIndexRequest;
use App\Features\Admin\Makers\Services\MakerManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(MakerIndexRequest $request, MakerManagementService $makers): View
    {
        $filters = $request->validated();

        return view('admin.makers.index', [
            'makers' => $makers->paginate($filters),
            'statuses' => $makers->statuses(),
            'filters' => $filters,
        ]);
    }
}
