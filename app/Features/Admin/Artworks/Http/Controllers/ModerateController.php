<?php
namespace App\Features\Admin\Artworks\Http\Controllers;
use App\Features\Admin\Artworks\Actions\ModerateArtworkAction;
use App\Features\Admin\Artworks\Http\Requests\ModerateArtworkRequest;
use App\Features\Admin\Artworks\Services\ArtworkManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class ModerateController extends Controller
{
    public function __invoke(ModerateArtworkRequest $request, int $artwork, ArtworkManagementService $service, ModerateArtworkAction $action): RedirectResponse
    {
        $action->execute($service->find($artwork), $request->validated());
        return to_route('admin.artworks.show', $artwork)->with('status', 'Artwork moderation status updated successfully.');
    }
}
