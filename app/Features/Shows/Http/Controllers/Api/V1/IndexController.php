<?php
namespace App\Features\Shows\Http\Controllers\Api\V1;
use App\Features\Shows\Http\Requests\MakerShowIndexRequest;
use App\Features\Shows\Http\Resources\ShowResource;
use App\Features\Shows\Services\MakerShowService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class IndexController extends Controller
{
    public function __invoke(MakerShowIndexRequest $request, MakerShowService $shows): JsonResponse
    {
        $paginator = $shows->paginate($request->user(), $request->validated());
        return ApiResponse::success(
            data: ShowResource::collection($paginator->getCollection())->resolve($request),
            message: 'Your shows were retrieved successfully.',
            meta: ['current_page'=>$paginator->currentPage(),'last_page'=>$paginator->lastPage(),'per_page'=>$paginator->perPage(),'total'=>$paginator->total()],
        );
    }
}
