<?php
namespace App\Features\Admin\Shows\Http\Controllers;
use App\Features\Admin\Shows\Http\Requests\UpdateShowRequest;
use App\Features\Admin\Shows\Services\ShowManagementService;
use App\Features\Shows\Actions\UpdateShowAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class UpdateController extends Controller
{
    public function __invoke(UpdateShowRequest $request, int $show, ShowManagementService $service, UpdateShowAction $action): RedirectResponse
    {
        $model = $service->find($show);
        $action->execute($model, $request->validated());
        return to_route('admin.shows.show', $show)->with('status', 'Show details and related artwork updated successfully.');
    }
}
