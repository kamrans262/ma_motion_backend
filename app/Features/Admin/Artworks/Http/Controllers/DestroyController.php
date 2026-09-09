<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Features\Artworks\Actions\DeleteArtworkAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class DestroyController extends Controller
{
    public function __invoke(int $artwork, ArtworkManagementService $service, DeleteArtworkAction $action): RedirectResponse
    {
        $action->execute($service->find($artwork));
        return to_route('admin.artworks.index')->with('status', 'Artwork deleted successfully.');
    }
}
