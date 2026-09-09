# MA Motion - Authentication, Roles & Accounts Foundation

## Scope

This milestone establishes the Laravel-side authentication and account foundation for the Flutter client.

Public account roles are:

- `maker`
- `appreciator`

The `admin` role exists for protected backoffice functionality but cannot be selected through public registration.

## API endpoints

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/logout`
- `GET /api/v1/me`

## Security choices

- Sanctum bearer tokens are used for mobile authentication.
- Passwords are hashed by the User model cast.
- Login/register/password-reset endpoints are rate limited.
- Inactive accounts cannot log in or use existing protected API tokens.
- Admin self-registration is rejected server-side.
- Forgot-password responses do not reveal whether an email exists.
- Successful password reset revokes existing API tokens.
- Roles and account status are enforced server-side.

## Social sign-in foundation

The `social_accounts` table is created for Google/Apple account linking. Provider-token verification is intentionally not implemented in this milestone because it requires provider-specific credentials/configuration. The account model is ready for that integration without changing the core user schema.

## Architecture

Feature-specific code lives under `app/Features/Auth`. Shared API response behavior remains under `app/Support/Api`. Controllers are thin and delegate business operations to focused Actions.
