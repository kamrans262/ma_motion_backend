<?php
namespace App\Features\Shows\Http\Controllers\Api\V1;
use App\Features\Shows\Http\Resources\ShowResource;
use App\Features\Shows\Services\MakerShowService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class ShowController extends Controller
{
    public function __invoke(Request $request, int $show, MakerShowService $shows): JsonResponse
    {
        $model = $shows->findOwned($request->user(), $show);
        return ApiResponse::success(data: ShowResource::make($model)->resolve($request), message: 'Show retrieved successfully.');
    }
}
