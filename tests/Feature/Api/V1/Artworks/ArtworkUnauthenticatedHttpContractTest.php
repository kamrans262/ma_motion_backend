<?php

namespace Tests\Feature\Api\V1\Artworks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ArtworkUnauthenticatedHttpContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_plain_http_request_to_protected_artwork_api_returns_standard_unauthenticated_json(): void
    {
        $this->get('/api/v1/me/artworks')
            ->assertUnauthorized()
            ->assertHeader('content-type', 'application/json')
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }

    public function test_existing_protected_me_endpoint_keeps_same_plain_http_unauthenticated_contract(): void
    {
        $this->get('/api/v1/me')
            ->assertUnauthorized()
            ->assertHeader('content-type', 'application/json')
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }
}
