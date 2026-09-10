# Notifications — Milestone 11

## Scope
This module implements the notification requirement explicitly supported by the MA Motion scope: an in-app alert when a Maker saved by an Appreciator announces a visible current/upcoming show, plus mobile Notification Settings.

## Delivery model
- In-app/database delivery only in this milestone.
- No Firebase/APNs/email/SMS dependency is introduced.
- Each recipient/show pair has a unique dedupe key, so editing the same show cannot spam duplicate alerts.
- Only active Appreciators with an existing Maker-save relationship are eligible.
- `saved_maker_show_alerts=false` suppresses future alerts without deleting notification history.
- Past or hidden shows do not generate alerts.
- Makers and Admins can continue managing shows exactly as before; announcement generation is integrated into the existing Show Actions.

## API
Authenticated active accounts:
- `GET /api/v1/me/notifications`
- `PATCH /api/v1/me/notifications/{notification}/read`
- `PATCH /api/v1/me/notifications/read-all`
- `GET /api/v1/me/notification-settings`
- `PATCH /api/v1/me/notification-settings`

## Admin
`GET /admin/notifications` is an operational read-only notification log with search/status/type filters and aggregate counts. It intentionally does not expose manual broadcast/send tooling because that is outside the original product scope.

## Future provider integration
Push notifications may later consume the same persisted notification records through a queue/event provider without changing the mobile API contract or the core show-notification business rule.
