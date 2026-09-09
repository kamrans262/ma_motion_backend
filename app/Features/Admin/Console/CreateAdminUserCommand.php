<?php

namespace App\Features\Admin\Console;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class CreateAdminUserCommand extends Command
{
    protected $signature = 'ma:admin:create
        {--name= : Administrator display name}
        {--email= : Administrator email address}
        {--password= : Password (prefer the interactive hidden prompt)}
        {--force : Replace an existing account with the same email}';

    protected $description = 'Create or explicitly replace a local MA Motion administrator account';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Administrator name', 'MA Administrator')));
        $email = Str::lower(trim((string) ($this->option('email') ?: $this->ask('Administrator email'))));
        $password = (string) ($this->option('password') ?: $this->secret('Administrator password'));

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existing = User::query()->where('email', $email)->first();

        if ($existing !== null && ! $this->option('force')) {
            $this->error('A user with this email already exists. Re-run with --force only if you intend to convert that account to an administrator.');

            return self::FAILURE;
        }

        $user = $existing ?? new User();
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'status' => UserStatus::Active,
        ])->save();

        if ($existing !== null) {
            $user->tokens()->delete();
        }

        $this->info("Administrator ready: {$user->email}");

        return self::SUCCESS;
    }
}
