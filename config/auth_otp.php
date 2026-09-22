<?php

return [
    'require_onboarding_verification' => (bool) env('OTP_REQUIRED_FOR_ONBOARDING', true),
    'expires_minutes' => 10,
    'resend_seconds' => 60,
    'maximum_attempts' => 5,
];
