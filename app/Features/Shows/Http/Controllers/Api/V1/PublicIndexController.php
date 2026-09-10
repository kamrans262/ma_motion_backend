<?php
namespace App\Features\Shows\Http\Controllers\Api\V1;
use App\Features\Shows\Http\Requests\PublicShowIndexRequest;
use App\Features\Shows\Http\Resources\ShowResource;
use App\Features\Shows\Services\PublicShowService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class PublicIndexController extends Controller
{
    public function __invoke(PublicShowIndexRequest $request, int $maker, PublicShowService $shows): JsonResponse
    {
        $paginator = $shows->paginateForMaker($maker, $request->validated());
        return ApiResponse::success(
            data: ShowResource::collection($paginator->getCollection())->resolve($request),
            message: 'Maker shows retrieved successfully.',
            meta: ['current_page'=>$paginator->currentPage(),'last_page'=>$paginator->lastPage(),'per_page'=>$paginator->perPage(),'total'=>$paginator->total()],
        );
    }
}
