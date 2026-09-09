<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Features\Artworks\Actions\DeleteArtworkMediaAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class DestroyMediaController extends Controller
{
    public function __invoke(int $artwork, int $media, ArtworkManagementService $service, DeleteArtworkMediaAction $action): RedirectResponse
    {
        $model=$service->find($artwork); $action->execute($model, $service->findMedia($model, $media), false);
        return to_route('admin.artworks.show', $artwork)->with('status', 'Artwork image removed successfully.');
    }
}
