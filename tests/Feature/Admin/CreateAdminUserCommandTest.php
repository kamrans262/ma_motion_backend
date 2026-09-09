<?php

namespace Tests\Feature\Admin;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_active_admin_account(): void
    {
        $this->artisan('ma:admin:create', [
            '--name' => 'Local Administrator',
            '--email' => 'local-admin@example.com',
            '--password' => 'SecureAdmin123',
        ])->assertSuccessful();

        $admin = User::query()->where('email', 'local-admin@example.com')->firstOrFail();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertSame(UserStatus::Active, $admin->status);
        $this->assertTrue(Hash::check('SecureAdmin123', $admin->password));
    }

    public function test_command_refuses_to_overwrite_existing_user_without_force(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->artisan('ma:admin:create', [
            '--name' => 'Administrator',
            '--email' => 'existing@example.com',
            '--password' => 'SecureAdmin123',
        ])->assertFailed();
    }
}
