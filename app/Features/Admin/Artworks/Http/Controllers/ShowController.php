<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
final class ShowController extends Controller
{
    public function __invoke(int $artwork, ArtworkManagementService $service): View
    {
        return view('admin.artworks.show', ['artwork'=>$service->find($artwork),'types'=>$service->types(),'styles'=>$service->styles(),'locations'=>$service->locations(),'moderationStatuses'=>$service->moderationStatuses()]);
    }
}
