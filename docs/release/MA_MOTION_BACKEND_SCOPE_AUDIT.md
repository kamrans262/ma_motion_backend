# MA Motion Backend Scope Audit — Milestone 15

This audit compares the original `MA (Motion Intro Integration) – Mobile App Scope` with the verified Laravel milestones.

## Backend scope status

- Authentication, roles, email/password login, password reset, logout: complete.
- Google/Apple backend identity-token verification: implemented in Milestone 15 using OIDC JWKS/RS256 verification. Live provider validation still requires the real Google/Apple client IDs and real-device tokens.
- Maker/Appreciator account model and basic profile setup APIs: complete.
- Featured Maker after login: backend/API and Admin management complete.
- Maker discovery, artwork grid/search, Type/Style/Show Status/location/city/postal/radius filters: complete.
- Maker public profile data, statistics, shows, and public artwork gallery endpoint: complete. The gallery endpoint was added in Milestone 15 to close the original profile-gallery scope explicitly.
- Hearts/Saved Makers and per-Appreciator relationships: complete.
- Maker profile-saved aggregate statistics: complete.
- Maker artwork CRUD/media/type/style/location and show relationships: complete.
- Shows/exhibitions CRUD, dates, current/upcoming/past derivation, location and related artwork: complete.
- Admin dashboard/users/makers/hearts/artwork/types/styles/locations/Featured Maker/notifications/content/settings/analytics/audit: complete.
- Terms & Conditions / Privacy Policy system pages and content management: complete as a CMS system. Actual legal wording remains a business/legal-content responsibility and must be reviewed and published by the project owner.
- In-app notification when a saved Maker announces a current/upcoming show: complete.

## Intentionally outside the Laravel backend

The original scope also contains Flutter/mobile deliverables. They are not missing backend code; they remain a separate frontend phase:

- opening Motion Intro integration/tweaks
- splash and onboarding screens
- role-selection UI
- Pinterest-style visual discovery screens
- search/filter UI
- Maker profile/gallery/show screens
- Saved Makers UI
- mobile notification screens/settings UI
- secure device token storage
- Google/Apple native SDK sign-in and passing the provider identity token to the Milestone 15 backend endpoint
- real-device responsive/performance tests and `flutter analyze` / `flutter test`

## Provider/deployment-dependent release items

These cannot be truthfully marked production-integrated from local backend tests alone:

1. Configure real `MA_GOOGLE_CLIENT_IDS` and/or `MA_APPLE_CLIENT_IDS` and enable the relevant provider flags.
2. Verify real Google and Apple sign-in tokens from the final Flutter bundle IDs/client IDs.
3. Configure production HTTPS, `APP_DEBUG=false`, secure session cookies, production DB/SMTP/cache/logging, storage link, backups and monitoring.
4. Run `php artisan ma:release:check --production` on the production host.
5. Add/publish legally reviewed Terms & Conditions and Privacy Policy content.

No additional functional Laravel module from the original scope is known to be missing after Milestone 15, subject to those provider/deployment items.
