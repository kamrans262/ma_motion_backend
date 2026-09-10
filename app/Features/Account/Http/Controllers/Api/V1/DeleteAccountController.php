<?php

namespace App\Features\Account\Http\Controllers\Api\V1;

use App\Features\Account\Actions\DeleteAccountAction;
use App\Features\Account\Http\Requests\DeleteAccountRequest;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class DeleteAccountController extends Controller
{
    public function __invoke(DeleteAccountRequest $request, DeleteAccountAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated());

        return ApiResponse::success(message: 'Account deleted successfully.');
    }
}
