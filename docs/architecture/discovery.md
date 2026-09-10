# MA Motion Discovery, Search & Advanced Filters API

Milestone 12 adds a dedicated read-only discovery module without duplicating Maker, Artwork, Show, Taxonomy, Location, or Save business logic.

## API routes

- `GET /api/v1/discovery` — primary Pinterest-style public artwork grid.
- `GET /api/v1/discovery/artworks` — explicit alias of the artwork discovery grid.
- `GET /api/v1/discovery/makers` — active Maker discovery with aggregate public statistics.
- `GET /api/v1/discovery/search` — unified Artwork + Maker search response using the same filters.
- `GET /api/v1/discovery/filters` — enabled Types, Styles, Show Status options, aliases, and radius capabilities.
- `GET /api/v1/discovery/locations` — active, paginated location autocomplete.

## Supported filters

`search`, `type_ids[]`, `style_ids[]`, `show_statuses[]`, `location_id`, `city`, `postal_code`, `country_code`, `latitude`, `longitude`, `radius_km`, `page`, `per_page`.

For simple clients, singular aliases `type_id`, `style_id`, and `show_status` are normalized to the corresponding array filters.

## Public visibility rules

Discovery never exposes inactive Makers, hidden Artwork, pending/rejected Artwork, Admin-only moderation fields, email addresses, or private save relationships. Maker Heart/Save data is returned only as the already-approved aggregate count.

A Show Status filter on Artwork means the Artwork is related to a visible Show in that derived status. A Show Status filter on Makers means the Maker has a visible Show in that derived status.

## Location semantics

For Artwork discovery, an Artwork's structured location takes precedence. If the Artwork has no structured location, its Maker profile location is used as a fallback. Maker discovery uses the Maker profile location.

City, postal code, country, and managed-location filters resolve to active `locations` rows. Radius filtering uses the existing latitude/longitude columns. It first applies a database-portable bounding box and then an exact Haversine calculation in PHP, avoiding a new geospatial dependency and remaining compatible with both MySQL and the test database.

Radius inputs must include latitude, longitude, and radius together. The supported radius is 1–500 km.

## Performance

All result endpoints are paginated (maximum 50 items per page), use eager loading for response relationships, reuse existing indexed foreign keys/location indexes, and avoid loading every Artwork/Show relationship into memory. Location radius candidates are pre-filtered by a bounding box before exact distance calculation.
