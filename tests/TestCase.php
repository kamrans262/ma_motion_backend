<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Legacy feature tests exercise existing onboarding contracts. OTP
        // enforcement is enabled explicitly in the dedicated OTP API tests.
        config(['auth_otp.require_onboarding_verification' => false]);
    }
}
