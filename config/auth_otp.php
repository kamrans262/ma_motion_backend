<?php

return [
    // Enable on Hostinger after deploying the mobile OTP flow. Retains the
    // existing legacy API contract for installations not yet migrated.
    'require_onboarding_verification' => (bool) env('OTP_REQUIRED_FOR_ONBOARDING', false),
    'expires_minutes' => 10,
    'resend_seconds' => 60,
    'maximum_attempts' => 5,
];
