<?php
namespace App\Features\Admin\Shows\Http\Controllers;
use App\Features\Admin\Shows\Http\Requests\ShowIndexRequest;
use App\Features\Admin\Shows\Services\ShowManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
final class IndexController extends Controller
{
    public function __invoke(ShowIndexRequest $request, ShowManagementService $service): View
    {
        $filters = $request->validated();
        return view('admin.shows.index', ['shows'=>$service->paginate($filters),'filters'=>$filters,'makers'=>$service->makers(),'locations'=>$service->locations(),'statuses'=>$service->statuses()]);
    }
}
