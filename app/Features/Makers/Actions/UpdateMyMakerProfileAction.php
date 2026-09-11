<?php

namespace App\Features\Makers\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateMyMakerProfileAction
{
    /** @param array<string, mixed> $data */
    public function execute(User $maker, array $data): User
    {
        return DB::transaction(function () use ($maker, $data): User {
            if (array_key_exists('name', $data) && $data['name'] !== null) {
                $maker->update(['name' => trim((string) $data['name'])]);
            }

            $profile = $maker->makerProfile()->firstOrCreate(['user_id' => $maker->id]);

            $profileData = [];

            foreach ([
                'bio',
                'location_text',
                'location_id',
                'website_url',
                'contact_email',
                'show_website_on_info_page',
                'show_email_on_info_page',
                'show_shows_on_info_page',
            ] as $key) {
                if (! array_key_exists($key, $data)) {
                    continue;
                }

                $value = $data[$key];

                if (is_string($value)) {
                    $value = trim($value);
                    $value = $value === '' ? null : $value;
                }

                $profileData[$key] = $value;
            }

            if (($data['complete_onboarding'] ?? false) === true) {
                $profileData['onboarding_completed_at'] = now();
            }

            if ($profileData !== []) {
                $profile->update($profileData);
            }

            if (array_key_exists('type_ids', $data)) {
                $profile->types()->sync(array_values($data['type_ids']));
            }

            if (array_key_exists('style_ids', $data)) {
                $profile->styles()->sync(array_values($data['style_ids']));
            }

            return $maker->refresh()->load([
                'makerProfile.location',
                'makerProfile.types',
                'makerProfile.styles',
                'makerProfile.carouselMedia',
            ]);
        });
    }
}
