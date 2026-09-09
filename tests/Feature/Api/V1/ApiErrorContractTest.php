<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class ApiErrorContractTest extends TestCase
{
    public function test_unknown_api_endpoint_returns_the_standard_not_found_contract(): void
    {
        $response = $this->getJson('/api/v1/this-endpoint-does-not-exist');

        $response
            ->assertNotFound()
            ->assertExactJson([
                'success' => false,
                'message' => 'API endpoint not found.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }

    public function test_invalid_http_method_returns_the_standard_method_not_allowed_contract(): void
    {
        $response = $this->postJson('/api/v1/health');

        $response
            ->assertStatus(405)
            ->assertExactJson([
                'success' => false,
                'message' => 'HTTP method not allowed for this endpoint.',
                'data' => null,
                'errors' => null,
                'meta' => null,
            ]);
    }
}
