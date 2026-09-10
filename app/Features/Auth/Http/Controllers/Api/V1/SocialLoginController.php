<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\SocialLoginAction;
use App\Features\Auth\Http\Requests\SocialLoginRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class SocialLoginController extends Controller
{
    public function __invoke(SocialLoginRequest $request, SocialLoginAction $action): JsonResponse
    {
        $provider = (string) $request->route('provider');
        $result = $action->execute($provider, $request->validated());

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])->resolve($request),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            message: ucfirst($provider).' login successful.',
        );
    }
}
