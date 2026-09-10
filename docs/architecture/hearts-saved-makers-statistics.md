# Milestone 09 — Hearts / Saved Makers & Statistics

## Scope
This module implements the MA Motion Heart / Save Maker relationship, Appreciator saved-Maker listing, Maker aggregate save statistics, Admin relationship visibility, Maker save counts in Admin, and the dashboard statistics that became available after Artwork and Shows were implemented.

## Data model
`maker_saves` is the authoritative relationship table between an Appreciator account and a Maker account. It has a unique `(appreciator_id, maker_id)` key so duplicate taps cannot create duplicate saves. Both foreign keys cascade on user deletion so account deletion does not leave orphaned relationships.

The save record is intentionally not soft-deleted. Unheart/remove is a relationship state change, not business content that needs restoration. The unique/index strategy supports efficient Maker counts and per-Appreciator saved lists.

## Authorization and privacy
Only active `appreciator` accounts may create/remove/read their saved Maker list. Only active `maker` accounts may access `/api/v1/me/maker-statistics`.

A Maker receives only the aggregate `profile_saved_count`; the API never exposes the identities of Appreciators who saved that Maker. Admin users may inspect the relationship directory because the original product scope explicitly requires that operational visibility.

Only active Maker accounts can be newly saved. If a Maker becomes inactive, the existing relationship is retained for Admin statistics/history but is excluded from the Appreciator's visible saved-Maker list. An Appreciator can still remove that inactive Maker relationship.

## API
- `GET /api/v1/me/saved-makers`
- `POST /api/v1/me/saved-makers/{maker}`
- `DELETE /api/v1/me/saved-makers/{maker}`
- `GET /api/v1/me/maker-statistics`

The public Maker resource now includes `saved_count`, an aggregate value only.

## Admin
- `GET /admin/saves` provides relationship search, totals, and a Most Hearted Makers leaderboard.
- Maker list/detail pages display aggregate save counts.
- Dashboard now displays Total Artwork, Total Shows, Maker Saves, Most Hearted Makers, and Recently Added Artwork in addition to existing account metrics.

## Performance
Saved-Maker lists and Admin relationship lists are paginated. Relations are eager-loaded, counts use SQL aggregate subqueries, and the migration adds indexes for both Maker-count and Appreciator-list access patterns. No new third-party package is required.
