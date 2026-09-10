# Milestone 14 — Dashboard, Analytics & Audit System

## Purpose
Milestone 14 completes the Admin dashboard/analytics requirement from the original MA Motion scope and adds an operational audit trail before release hardening.

## Analytics
- `/admin/analytics` reads only live database records; no fake metrics are used.
- Reporting periods are intentionally bounded to 7, 30, or 90 days.
- Metrics cover users/roles/status, artwork moderation and visibility, shows by derived timeline status, Maker saves, notifications, and Admin write activity.
- Daily activity aggregation remains database-backed and bounded by the selected reporting window.
- `/admin/analytics/export` provides the same report as CSV for operational reporting.

## Audit system
- `admin_audit_logs` is append-oriented and has no Admin update/delete endpoint.
- `LogAdminActivity` records authenticated Admin write requests, including successful Admin login/logout and platform mutations.
- Request payloads are data-minimized. The audit summary stores field names and only a small allow-list of operational values. Passwords, emails, names, legal/body copy, bios, descriptions, tokens, and uploaded file contents are never persisted in the audit request JSON.
- `/admin/audit-logs` supports operational search/filtering and `/admin/audit-logs/export` exports up to 5,000 matching rows.

## Preserved architecture
Thin controllers, Form Requests, focused Services, existing MA Motion Blade design system, `/assets/admin` routing, and the polished sidebar scrollbar are preserved. No new analytics package or geospatial dependency is introduced.
