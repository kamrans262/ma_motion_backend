# Featured Maker — Milestone 10

## Purpose
Milestone 10 implements the single post-login Featured Maker spotlight defined in the original MA Motion scope.

## Backend design
- `featured_maker_settings` stores one named `post_login` slot rather than scattering a `featured` flag across Maker accounts.
- The setting references an active Maker and optionally one approved/visible artwork owned by that Maker.
- The setting also stores lightweight spotlight content: eyebrow, headline and description.
- Admin changes are tracked with `updated_by`.
- Deleting the configuration never deletes Maker, artwork, show or save data.

## API
`GET /api/v1/featured-maker`

The route requires an authenticated active account because the product scope places this experience after registered-user login. Both Maker and Appreciator accounts may consume it.

The server returns no spotlight when:
- no configuration exists,
- the configuration is unpublished,
- the selected Maker is inactive or no longer available.

Only approved and visible artwork is exposed. Maker email is not exposed.

## Admin
`/admin/featured-maker` allows Admin to:
- select/change the active Maker,
- manage spotlight text,
- optionally select a spotlight artwork,
- publish/unpublish,
- remove the spotlight configuration.

The sidebar's vertical navigation scrollbar is also refined in this milestone using standards-based Firefox properties and WebKit scrollbar selectors while preserving the MA Motion dark-green/purple design system.

## Safety
- No `public/admin` directory.
- No third-party package.
- Existing `/assets/admin` path retained.
- Spotlight artwork ownership and public visibility are verified server-side.
- Existing M06 spacing, M07 guest-auth, M08 show UI, and M09 save/statistics contracts remain regression-tested.
