<?php

namespace App\Features\Auth\Actions;

use App\Features\Auth\Contracts\SocialIdentityVerifier;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Auth\Exceptions\InactiveAccountException;
use App\Features\Auth\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SocialLoginAction
{
    public function __construct(private readonly SocialIdentityVerifier $verifier) {}

    /** @param array{id_token:string,role?:string|null,name?:string|null,device_name?:string|null} $data
     *  @return array{user:User,token:string}
     */
    public function execute(string $provider, array $data): array
    {
        $identity = $this->verifier->verify($provider, $data['id_token']);

        return DB::transaction(function () use ($provider, $data, $identity): array {
            $linked = SocialAccount::query()
                ->with('user')
                ->where('provider', $provider)
                ->where('provider_user_id', $identity->subject)
                ->first();

            if ($linked !== null) {
                $linked->update(['provider_email' => $identity->email]);
                return $this->issueToken($linked->user, $data['device_name'] ?? null);
            }

            if ($identity->email === null || ! $identity->emailVerified) {
                throw ValidationException::withMessages([
                    'id_token' => ['A verified email address is required to create or link an MA Motion account.'],
                ]);
            }

            $user = User::query()->whereRaw('LOWER(email) = ?', [$identity->email])->first();
            if ($user !== null) {
                if ($user->hasRole(UserRole::Admin)) {
                    throw new AuthorizationException();
                }
                if (! $user->isActive()) {
                    throw new InactiveAccountException();
                }
                $existingProvider = $user->socialAccounts()->where('provider', $provider)->first();
                if ($existingProvider !== null && $existingProvider->provider_user_id !== $identity->subject) {
                    throw ValidationException::withMessages([
                        'id_token' => ['This provider is already linked to a different identity for the account.'],
                    ]);
                }
                if ($user->email_verified_at === null) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            } else {
                $roleValue = trim((string) ($data['role'] ?? ''));
                $name = trim((string) ($data['name'] ?? $identity->name ?? ''));
                if (! in_array($roleValue, [UserRole::Maker->value, UserRole::Appreciator->value], true)) {
                    throw ValidationException::withMessages(['role' => ['The role field is required for a new social account.']]);
                }
                if ($name === '') {
                    throw ValidationException::withMessages(['name' => ['The name field is required for a new social account.']]);
                }

                $role = UserRole::from($roleValue);
                $user = User::query()->create([
                    'name' => $name,
                    'email' => $identity->email,
                    'password' => Str::random(64),
                    'role' => $role,
                    'status' => UserStatus::Active,
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                if ($role === UserRole::Maker) {
                    $user->makerProfile()->create();
                }
            }

            SocialAccount::query()->create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $identity->subject,
                'provider_email' => $identity->email,
            ]);

            return $this->issueToken($user, $data['device_name'] ?? null);
        });
    }

    /** @return array{user:User,token:string} */
    private function issueToken(User $user, ?string $deviceName): array
    {
        if ($user->hasRole(UserRole::Admin)) {
            throw new AuthorizationException();
        }
        if (! $user->isActive()) {
            throw new InactiveAccountException();
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $name = trim((string) $deviceName);
        $token = $user->createToken($name !== '' ? $name : 'MA Motion mobile', ['mobile'])->plainTextToken;

        return ['user' => $user->refresh(), 'token' => $token];
    }
}
