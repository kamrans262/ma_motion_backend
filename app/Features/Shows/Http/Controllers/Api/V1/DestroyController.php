<?php
namespace App\Features\Shows\Http\Controllers\Api\V1;
use App\Features\Shows\Actions\DeleteShowAction;
use App\Features\Shows\Services\MakerShowService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class DestroyController extends Controller
{
    public function __invoke(Request $request, int $show, MakerShowService $shows, DeleteShowAction $action): JsonResponse
    {
        $model = $shows->findOwned($request->user(), $show);
        $action->execute($model);
        return ApiResponse::success(message: 'Show deleted successfully.');
    }
}
