<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class HealthApiTest extends TestCase
{
    public function test_health_endpoint_returns_the_standard_api_contract(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'message' => 'API is healthy.',
                'data' => [
                    'service' => 'MA Motion API',
                    'version' => 'v1',
                ],
                'errors' => null,
                'meta' => null,
            ]);
    }
}
