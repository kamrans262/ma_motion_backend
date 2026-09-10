<?php

namespace App\Features\Admin\Saves\Http\Controllers;

use App\Features\Admin\Saves\Http\Requests\SaveIndexRequest;
use App\Features\Admin\Saves\Services\SaveManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class IndexController extends Controller
{
    public function __invoke(SaveIndexRequest $request, SaveManagementService $saves): View
    {
        $filters = $request->validated();

        return view('admin.saves.index', [
            'saves' => $saves->paginate($filters),
            'summary' => $saves->summary(),
            'topMakers' => $saves->topMakers(),
            'filteredMaker' => $saves->filteredMaker(isset($filters['maker_id']) ? (int) $filters['maker_id'] : null),
            'filters' => $filters,
        ]);
    }
}
