# MA Motion Admin Panel Foundation

Milestone 04 establishes the first production-oriented web backoffice layer for MA Motion.

## Architecture

- Session-based web authentication is independent of the Flutter/Sanctum token flow.
- Only active users with the `admin` role may enter protected admin routes.
- Login attempts are rate limited.
- Login regenerates the session ID; logout invalidates the session and regenerates the CSRF token.
- Controllers are thin and delegate authentication/dashboard logic to Actions and Services.
- Admin functionality is grouped under `App\Features\Admin`.

## Routes

- `GET /admin/login` - admin login form
- `POST /admin/login` - session authentication
- `GET /admin` - protected dashboard
- `POST /admin/logout` - protected logout

## UI system

The visual system follows the approved MA Motion mobile reference:

- Deep green / near-black base
- Vivid purple accent and outlined controls
- Instrument Sans for headings
- Arial for body and operational text
- Responsive sidebar and tables from compact mobile through large desktop
- Accessible focus states, skip link and reduced-motion support

Tokens are centralized in `public/assets/admin/css/admin.css`. Future admin modules should reuse this design system instead of adding competing styles.

## Local administrator creation

Use the interactive command:

`php artisan ma:admin:create`

The password is requested with a hidden prompt. The `--force` option is intentionally required before an existing account can be converted into an administrator; existing API tokens are revoked in that case.

## Scope boundary

This milestone creates the admin shell, security boundary, dashboard foundation and reusable responsive UI. Full Users/Makers, Artwork, Shows, Categories, Locations, Featured Maker, Notifications, CMS, analytics and settings management are implemented in subsequent modules.
