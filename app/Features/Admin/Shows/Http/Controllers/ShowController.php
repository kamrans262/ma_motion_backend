<?php
namespace App\Features\Admin\Shows\Http\Controllers;
use App\Features\Admin\Shows\Services\ShowManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
final class ShowController extends Controller
{
    public function __invoke(int $show, ShowManagementService $service): View
    {
        $model = $service->find($show);
        return view('admin.shows.show', ['show'=>$model,'locations'=>$service->locations(),'makerArtworks'=>$service->makerArtworks($model->maker),'statuses'=>$service->statuses()]);
    }
}
