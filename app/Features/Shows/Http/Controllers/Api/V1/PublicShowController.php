<?php
namespace App\Features\Shows\Http\Controllers\Api\V1;
use App\Features\Shows\Http\Resources\ShowResource;
use App\Features\Shows\Services\PublicShowService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class PublicShowController extends Controller
{
    public function __invoke(Request $request, int $maker, int $show, PublicShowService $shows): JsonResponse
    {
        $model = $shows->findForMaker($maker, $show);
        return ApiResponse::success(data: ShowResource::make($model)->resolve($request), message: 'Maker show retrieved successfully.');
    }
}
