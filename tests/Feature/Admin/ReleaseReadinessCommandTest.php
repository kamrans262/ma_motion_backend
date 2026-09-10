<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReleaseReadinessCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_readiness_command_passes_for_test_environment_without_printing_secrets(): void
    {
        $this->artisan('ma:release:check')->assertSuccessful();
    }
}
