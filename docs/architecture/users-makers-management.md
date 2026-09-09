# Milestone 05 — Users & Makers Management

## Purpose
This milestone activates the Users and Makers areas of the MA Motion Admin Panel and introduces the core Maker profile domain/API used by Flutter.

## Admin capabilities
- Search/filter/paginate all users.
- Review and update user name, email and active/inactive status.
- Deactivation revokes mobile API tokens.
- Delete non-admin user accounts with destructive confirmation.
- Administrator accounts are protected from deletion in User Management.
- Current administrator cannot deactivate their own session account.
- Search/filter/paginate Maker accounts.
- Update Maker account status, bio and location label.

## Maker domain
`maker_profiles` is one-to-one with `users` and stores Maker-only profile data. Existing Maker accounts are backfilled during migration; new Maker registration creates the profile transactionally.

Current fields:
- bio
- location_text
- profile_image_path (storage slot only; secure upload is intentionally deferred to the media milestone)

Structured City/ZIP/radius discovery is expanded in the Location milestone. Artwork, shows and save statistics remain separate domain milestones.

## Mobile API
Public:
- `GET /api/v1/makers`
- `GET /api/v1/makers/{id}`

Authenticated Maker:
- `PATCH /api/v1/me/maker-profile`

Public Maker resources intentionally do not expose email addresses or account status internals.

## Safety
- Admin-only web routes remain behind `admin.access`.
- Public Maker discovery returns active Maker accounts only.
- Role checks remain server-side.
- User deactivation revokes tokens.
- Role escalation is not exposed in normal User Management.
- User/Maker lists are paginated.
