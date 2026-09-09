<?php

namespace App\Features\Makers\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateMyMakerProfileAction
{
    /** @param array{name?:string|null,bio?:string|null,location_text?:string|null} $data */
    public function execute(User $maker, array $data): User
    {
        return DB::transaction(function () use ($maker, $data): User {
            if (array_key_exists('name', $data)) {
                $maker->update(['name' => trim((string) $data['name'])]);
            }

            $profileData = [];
            foreach (['bio', 'location_text'] as $key) {
                if (array_key_exists($key, $data)) {
                    $value = $data[$key];
                    $profileData[$key] = $value === null ? null : $this->nullableTrim((string) $value);
                }
            }

            if ($profileData !== []) {
                $maker->makerProfile()->updateOrCreate(
                    ['user_id' => $maker->id],
                    $profileData,
                );
            } else {
                $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);
            }

            return $maker->refresh()->load('makerProfile');
        });
    }

    private function nullableTrim(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
