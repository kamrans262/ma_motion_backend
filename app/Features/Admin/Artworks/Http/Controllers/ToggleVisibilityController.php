<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Actions\ToggleArtworkVisibilityAction;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class ToggleVisibilityController extends Controller
{
    public function __invoke(int $artwork, ArtworkManagementService $service, ToggleArtworkVisibilityAction $action): RedirectResponse
    {
        $model=$action->execute($service->find($artwork));
        return to_route('admin.artworks.show', $model)->with('status', $model->is_visible ? 'Artwork is visible again.' : 'Artwork has been hidden.');
    }
}
