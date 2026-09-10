<?php
namespace App\Features\Admin\Shows\Http\Controllers;
use App\Features\Admin\Shows\Services\ShowManagementService;
use App\Features\Shows\Actions\DeleteShowAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class DestroyController extends Controller
{
    public function __invoke(int $show, ShowManagementService $service, DeleteShowAction $action): RedirectResponse
    {
        $action->execute($service->find($show));
        return to_route('admin.shows.index')->with('status', 'Show deleted successfully.');
    }
}
