<?php
namespace App\Features\Admin\Shows\Http\Controllers;
use App\Features\Admin\Shows\Actions\ToggleShowVisibilityAction;
use App\Features\Admin\Shows\Services\ShowManagementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class ToggleVisibilityController extends Controller
{
    public function __invoke(int $show, ShowManagementService $service, ToggleShowVisibilityAction $action): RedirectResponse
    {
        $model = $action->execute($service->find($show));
        return to_route('admin.shows.show', $model)->with('status', $model->is_visible ? 'Show is visible again.' : 'Show has been hidden from public Maker profiles.');
    }
}
