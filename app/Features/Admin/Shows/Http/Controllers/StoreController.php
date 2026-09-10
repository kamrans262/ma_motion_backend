<?php
namespace App\Features\Admin\Shows\Http\Controllers;
use App\Features\Admin\Shows\Http\Requests\StoreShowRequest;
use App\Features\Admin\Shows\Services\ShowManagementService;
use App\Features\Shows\Actions\CreateShowAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
final class StoreController extends Controller
{
    public function __invoke(StoreShowRequest $request, ShowManagementService $service, CreateShowAction $action): RedirectResponse
    {
        $data = $request->validated();
        $maker = $service->findMaker((int) $data['maker_id']);
        unset($data['maker_id']);
        $show = $action->execute($maker, $data);
        return to_route('admin.shows.show', $show)->with('status', 'Show created successfully. You can now attach related artwork.');
    }
}
