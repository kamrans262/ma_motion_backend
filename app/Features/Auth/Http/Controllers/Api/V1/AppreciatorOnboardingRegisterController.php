<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\RegisterAppreciatorOnboardingAction;
use App\Features\Auth\Http\Requests\AppreciatorOnboardingRegisterRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Features\Auth\Services\EmailOtpService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class AppreciatorOnboardingRegisterController extends Controller
{
    public function __invoke(
        AppreciatorOnboardingRegisterRequest $request,
        RegisterAppreciatorOnboardingAction $action,
        EmailOtpService $otp,
    ): JsonResponse {
        $data = $request->validated();
        $id = $data['otp_challenge_id'] ?? null;
        $result = DB::transaction(function () use ($action, $otp, $data, $id): array {
            if ($id !== null || config('auth_otp.require_onboarding_verification')) {
                $otp->consumeRegistrationProof($id, $data['email']);
            }
            $created = $action->execute($data);
            if ($id !== null) {
                $created['user']->forceFill(['email_verified_at' => now()])->save();
            }
            return $created;
        });

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])->resolve($request),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            message: 'Appreciator onboarding completed successfully.',
            status: 201,
        );
    }
}
