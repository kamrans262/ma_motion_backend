# MA Motion Backend Release Checklist

## Local release gate

1. Use the project WAMP PHP runtime (PHP 8.4.0) with required extensions including GD/OpenSSL/cURL/intl/fileinfo/pdo_mysql.
2. Run Milestone 15 module tests and the full regression suite.
3. Verify `config:cache`, `route:cache`, and `view:cache` can be built successfully.
4. Confirm `.env` is not tracked and `public/admin` does not exist.
5. Run `php artisan ma:release:check`.

## Production gate

- Use HTTPS and set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`.
- Store secrets only in production environment/secret storage.
- Configure real database, SMTP, cache/session/logging settings.
- Configure Google/Apple client IDs only for providers actually enabled.
- Run database migrations with a backup/rollback plan.
- Ensure `storage` and `bootstrap/cache` are writable and public storage is linked when media is served locally.
- Run `php artisan optimize:clear`, then production cache commands.
- Run `php artisan ma:release:check --production`.
- Perform real-server smoke tests for Admin login, API health, authenticated mobile flows, uploads, discovery and social login.
- Keep monitoring/log rotation/backups enabled and verify restore procedures.

The release command intentionally never prints secret values.
