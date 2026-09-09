# Milestone 06 — Types, Styles & Location Management

Milestone 06 adds the managed discovery taxonomy and location foundation required by the original MA Motion scope.

## Architecture
- Domain models: `App\Features\Taxonomy\Models` and `App\Features\Locations\Models`.
- Admin management remains feature-first under `App\Features\Admin\Taxonomy` and `App\Features\Admin\Locations`.
- Controllers are thin; Form Requests own validation; Actions own writes; Services own filtered pagination.
- Existing MA Motion Admin Blade/CSS design system is reused. No `public/admin` directory is created.

## Data model
- `artwork_types`: name, stable slug, enabled state, sort order, soft deletion.
- `artwork_styles`: name, stable slug, enabled state, sort order, soft deletion.
- `locations`: city, region, ZIP/postal, ISO alpha-2 country code, optional latitude/longitude, enabled state, sort order, soft deletion.
- Coordinates are indexed as a future-friendly foundation. No geospatial package or premature spatial database dependency is introduced.
- `maker_profiles.location_id` optionally links Makers to a managed location while preserving the existing `location_text` field for backward compatibility.

## Default taxonomy
`MaMotionTaxonomySeeder` idempotently creates the Type and Style examples listed in the authoritative scope. It never overwrites an existing record.

## Deletion policy
Types, Styles and Locations use soft deletion so Admin deletion does not destroy historical records that later artwork/show/maker references may need.

## Deferred intentionally
- Artwork references to Type/Style belong to Milestone 07.
- Show location links belong to Milestone 08.
- Public/mobile discovery and radius calculations belong to Milestone 10.
