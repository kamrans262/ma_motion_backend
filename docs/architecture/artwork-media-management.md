# Milestone 07 — Artwork & Media Management

## Scope implemented

This milestone implements the original MA Motion scope for Maker artwork management and Admin artwork moderation without rebuilding discovery/search (Milestone 10) or Shows/Exhibitions (Milestone 08).

### Maker API
- authenticated Maker-only artwork list/detail/create/update/delete
- Type, Style and managed Location associations from Milestone 06
- optional public-facing location label
- multiple artwork images per artwork (1–10)
- JPG, PNG and WebP only; SVG and arbitrary files are rejected
- generated storage filenames; original client filenames are never trusted
- server-detected MIME type, byte size and dimensions stored with each image
- primary-image selection
- image removal while preventing the last image from being removed
- Maker edits/media changes reset moderation to `pending`
- ownership checks return 404 for another Maker's artwork
- soft deletion for artwork records
- hard deletion of a Maker account cleans up that Maker's stored artwork image files after the database cascade

### Admin
- searchable/filterable artwork directory
- filters for Maker, Type, Style, Location, moderation and visibility
- artwork detail/edit screen
- approve, reject with required reason, or return to pending
- hide/unhide independently of moderation
- inspect uploaded image metadata
- set primary image
- remove non-last images
- soft-delete artwork

## Data model

`artworks` belongs to Maker (`users`), Type, Style and Location. It stores moderation and visibility independently so future public discovery can require both `approved` and visible without changing the schema.

`artwork_media` stores media metadata separately from the artwork record. Milestone 07 intentionally enables image media only because the supplied product scope specifies a Pinterest-style visual artwork grid and does not require video upload. The separate media table leaves room for later supported media kinds without redesigning artwork records.

Files use Laravel's existing `public` filesystem disk under generated paths:
`artworks/{maker_id}/{artwork_id}/{uuid}.{safe_extension}`.

## Deliberately deferred
- public artwork discovery/grid endpoints and advanced filters: Milestone 10
- shows/exhibitions and artwork-show relations: Milestone 08
- Featured Maker: Milestone 11
- dashboard analytics/audit expansion: Milestone 14
- video processing/transcoding: not present in supplied scope

## Security / operational notes
- server-side role middleware and ownership checks
- active/non-deleted taxonomy/location validation for Maker writes
- Admin writes remain behind `admin.access`
- image MIME/extension/size/dimension validation
- SVG excluded
- max 10 images, 10 MB each, max 12000×12000
- generated filenames prevent path/client-name injection
- database mutations use transactions where multiple records change
- public storage link is ensured by the installer for local image rendering
