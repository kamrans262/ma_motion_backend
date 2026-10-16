<?php

namespace Tests\Feature\Api\V1\Shows;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class EventLocationSuggestionsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_authenticated_active_maker_can_search_event_locations(): void
    {
        $this->getJson('/api/v1/me/shows/event-location-suggestions?input=gallery')
            ->assertUnauthorized();

        $appreciator = User::factory()->create([
            'role' => UserRole::Appreciator,
            'status' => UserStatus::Active,
        ]);
        $token = $appreciator->createToken('mobile', ['mobile'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/me/shows/event-location-suggestions?input=gallery')
            ->assertForbidden();
    }

    public function test_it_returns_google_place_descriptions_without_exposing_the_key(): void
    {
        config()->set('services.google_places.api_key', 'test-server-key');
        Http::fake([
            'places.googleapis.com/*' => Http::response([
                'suggestions' => [
                    ['placePrediction' => ['text' => ['text' => 'Art Gallery, Main Street, Chicago, IL, USA']]],
                    ['placePrediction' => ['text' => ['text' => 'Museum of Art, Chicago, IL, USA']]],
                ],
            ]),
        ]);

        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/me/shows/event-location-suggestions?input=Art%20Gallery');

        $response->assertOk()
            ->assertJsonPath('data.0.description', 'Art Gallery, Main Street, Chicago, IL, USA')
            ->assertJsonCount(2, 'data');
        $this->assertStringNotContainsString('test-server-key', $response->getContent());

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://places.googleapis.com/v1/places:autocomplete'
            && $request->hasHeader('X-Goog-Api-Key', 'test-server-key')
            && $request['input'] === 'Art Gallery'
        );
    }

    public function test_invalid_input_is_rejected_without_calling_google(): void
    {
        Http::fake();
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me/shows/event-location-suggestions?input=x')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('input');
        Http::assertNothingSent();
    }

    public function test_missing_server_key_returns_a_graceful_error(): void
    {
        config()->set('services.google_places.api_key', '');
        Http::fake();
        $maker = User::factory()->create([
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
        ]);
        $token = $maker->createToken('mobile', ['mobile'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me/shows/event-location-suggestions?input=gallery')
            ->assertStatus(503)
            ->assertJsonPath('code', 'PLACES_NOT_CONFIGURED');
        Http::assertNothingSent();
    }
}
