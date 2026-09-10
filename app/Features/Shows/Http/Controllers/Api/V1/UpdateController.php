<?php
namespace App\Features\Shows\Http\Controllers\Api\V1;
use App\Features\Shows\Actions\UpdateShowAction;
use App\Features\Shows\Http\Requests\UpdateMyShowRequest;
use App\Features\Shows\Http\Resources\ShowResource;
use App\Features\Shows\Services\MakerShowService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class UpdateController extends Controller
{
    public function __invoke(UpdateMyShowRequest $request, int $show, MakerShowService $shows, UpdateShowAction $action): JsonResponse
    {
        $model = $shows->findOwned($request->user(), $show);
        $model = $action->execute($model, $request->validated());
        return ApiResponse::success(data: ShowResource::make($model)->resolve($request), message: 'Show updated successfully.');
    }
}
