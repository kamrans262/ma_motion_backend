# Milestone 08 — Shows & Exhibitions

## Scope
Milestone 08 implements the original MA Motion requirement that Makers can add and manage shows/exhibitions with a name, description, location, start/end dates, current/upcoming status and related artwork. It also gives the Admin Panel operational control over shows and exposes current/upcoming public show information for Maker profiles.

## Architecture
- `App\Features\Shows` owns show domain logic, Maker/public API controllers, validation, resources, status calculation and artwork-linking rules.
- `App\Features\Admin\Shows` owns Admin-specific requests, queries, controllers and visibility control.
- Controllers remain thin; database/business behavior lives in Actions and Services.
- Existing `Location`, `Artwork` and `User` models are extended only with relationships needed by this milestone.

## Status rule
`current`, `upcoming` and `past` are derived from `start_date` and `end_date` at request time. No mutable status column is stored, preventing stale show status data.

## Related artwork security
A show can only reference artwork owned by the same Maker. `ShowArtworkSyncService` enforces this server-side for both Maker and Admin flows. Public show resources further restrict related artwork to approved + visible artwork.

## Visibility/public behavior
- Makers can manage all of their own shows, including past shows.
- Admin can hide/unhide a show.
- Public Maker show endpoints expose only visible current/upcoming shows belonging to active Maker accounts.
- Show deletion is soft; deleting a show never deletes its related artwork.

## Database
- `shows`: Maker, optional structured Location, flexible public venue label, dates, visibility, sort order, soft deletes.
- `show_artwork`: many-to-many relationship with stable relationship sort order.

## API
Maker protected endpoints:
- `GET /api/v1/me/shows`
- `POST /api/v1/me/shows`
- `GET /api/v1/me/shows/{show}`
- `PATCH /api/v1/me/shows/{show}`
- `DELETE /api/v1/me/shows/{show}`

Public Maker-profile endpoints:
- `GET /api/v1/makers/{maker}/shows`
- `GET /api/v1/makers/{maker}/shows/{show}`

## Admin
- `/admin/shows`
- `/admin/shows/{show}`
- create, search/filter, edit, artwork relationship management, hide/unhide and soft delete
- responsive MA Motion design system; no separate UI framework introduced
