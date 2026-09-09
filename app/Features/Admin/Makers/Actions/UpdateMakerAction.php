<?php

namespace App\Features\Admin\Makers\Actions;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UpdateMakerAction
{
    /** @param array{name:string,email:string,status:string,bio?:string|null,location_text?:string|null} $data */
    public function execute(User $maker, array $data): User
    {
        if (! $maker->hasRole(UserRole::Maker)) {
            throw (new ModelNotFoundException())->setModel(User::class, [$maker->getKey()]);
        }

        return DB::transaction(function () use ($maker, $data): User {
            $wasActive = $maker->isActive();

            $maker->update([
                'name' => trim($data['name']),
                'email' => Str::lower(trim($data['email'])),
                'status' => UserStatus::from($data['status']),
            ]);

            $maker->makerProfile()->updateOrCreate(
                ['user_id' => $maker->id],
                [
                    'bio' => $this->nullableTrim($data['bio'] ?? null),
                    'location_text' => $this->nullableTrim($data['location_text'] ?? null),
                ],
            );

            if ($wasActive && ! $maker->isActive()) {
                $maker->tokens()->delete();
            }

            return $maker->refresh()->load('makerProfile');
        });
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
