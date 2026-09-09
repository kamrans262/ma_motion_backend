<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Actions\UpdateArtworkAction;
use App\Features\Admin\Artworks\Http\Requests\UpdateArtworkRequest;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class UpdateController extends Controller
{
    public function __invoke(UpdateArtworkRequest $request, int $artwork, ArtworkManagementService $service, UpdateArtworkAction $action): RedirectResponse
    {
        $action->execute($service->find($artwork), $request->validated());
        return to_route('admin.artworks.show', $artwork)->with('status', 'Artwork details updated successfully.');
    }
}
