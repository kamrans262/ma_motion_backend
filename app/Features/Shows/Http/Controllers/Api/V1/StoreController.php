<?php
namespace App\Features\Shows\Http\Controllers\Api\V1;
use App\Features\Shows\Actions\CreateShowAction;
use App\Features\Shows\Http\Requests\StoreShowRequest;
use App\Features\Shows\Http\Resources\ShowResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class StoreController extends Controller
{
    public function __invoke(StoreShowRequest $request, CreateShowAction $action): JsonResponse
    {
        $show = $action->execute($request->user(), $request->validated());
        return ApiResponse::success(data: ShowResource::make($show)->resolve($request), message: 'Show created successfully.', status: 201);
    }
}
