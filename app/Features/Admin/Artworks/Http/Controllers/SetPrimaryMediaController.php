<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Features\Artworks\Actions\SetPrimaryArtworkMediaAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class SetPrimaryMediaController extends Controller
{
    public function __invoke(int $artwork, int $media, ArtworkManagementService $service, SetPrimaryArtworkMediaAction $action): RedirectResponse
    {
        $model=$service->find($artwork); $action->execute($model, $service->findMedia($model, $media));
        return to_route('admin.artworks.show', $artwork)->with('status', 'Primary artwork image updated successfully.');
    }
}
