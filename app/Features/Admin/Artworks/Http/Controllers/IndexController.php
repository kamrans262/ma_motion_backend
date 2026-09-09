<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Http\Requests\ArtworkIndexRequest;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
final class IndexController extends Controller
{
    public function __invoke(ArtworkIndexRequest $request, ArtworkManagementService $service): View
    {
        $filters=$request->validated();
        return view('admin.artworks.index', ['artworks'=>$service->paginate($filters),'filters'=>$filters,'makers'=>$service->makers(),'types'=>$service->types(),'styles'=>$service->styles(),'locations'=>$service->locations(),'moderationStatuses'=>$service->moderationStatuses()]);
    }
}
