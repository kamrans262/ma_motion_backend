# MA Motion Settings, Account & App Content

Milestone 13 completes the Settings & Content requirements from the original MA Motion scope without rebuilding Notification Settings or Logout, which already exist.

## Mobile/API account settings

Authenticated active accounts receive:
- `PATCH /api/v1/me/profile` — update the account display name.
- `PUT /api/v1/me/password` — verify the current password, change it, and revoke other Sanctum sessions.
- `DELETE /api/v1/me/account` — password + explicit `DELETE` confirmation, then delete the account and owned stored files.

Maker-only profile image endpoints:
- `POST /api/v1/me/profile-image`
- `DELETE /api/v1/me/profile-image`

Profile images accept only JPG/JPEG, PNG, or WebP images, max 5 MB and max 8000x8000 pixels. Replacements delete the previous file only after the database update succeeds.

The existing Milestone 11 Notification Settings API and existing Auth Logout endpoint remain the source of truth; this milestone does not duplicate them.

## App content / legal API

Public read-only endpoints:
- `GET /api/v1/content`
- `GET /api/v1/content/{slug}`

Only published pages with a publish timestamp are returned. The required system slugs are:
- `terms-and-conditions`
- `privacy-policy`

They are seeded as protected drafts intentionally. MA Motion administrators must provide the approved legal text before publishing; the backend does not invent legal policy content.

## Admin Content

`/admin/content` supports search, publish/draft filtering, creation of additional basic app content, editing, publish/unpublish, and soft deletion of optional pages. Required Terms & Conditions and Privacy Policy pages cannot be deleted and their slugs cannot be changed.

## Admin Settings

`/admin/settings` lets the signed-in Admin update their own name/email and change their password. Admin password changes revoke Admin API tokens while preserving the authenticated browser session.

## Account deletion

The reusable `AccountDeletionService` is shared by self-service account deletion and existing Admin User Management deletion. Before deleting a Maker it captures Artwork media and Maker profile-image paths, deletes database ownership inside a transaction, then removes stored files. Administrator accounts remain protected from both deletion flows.
