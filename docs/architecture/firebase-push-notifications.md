# MA Motion — Firebase Cloud Messaging

The backend extends the existing M11 in-app notification system with FCM delivery to Android and iOS devices. It does not introduce a general marketing broadcast system.

## Authenticated mobile API

- POST /api/v1/me/push-tokens with JSON fields token (FCM registration token) and platform (android or ios).
- DELETE /api/v1/me/push-tokens with JSON field token.
- Existing GET /api/v1/me/notifications, mark-read, mark-all-read, and notification-settings APIs remain unchanged.

## Data and security

push_device_tokens has user_id, unique token_hash (SHA-256), token, platform, last_seen_at and timestamps. User deletion cascades device-token removal. An FCM token is rebound to the latest signed-in user on that device. Unregister can only delete tokens belonging to the currently authenticated user.

Keep a Firebase service account JSON in a private, server-only directory. Never commit it or expose it through a web root.

In production Laravel .env set:
MA_FCM_ENABLED=true
MA_FCM_PROJECT_ID=<actual-firebase-project-id>
MA_FCM_CREDENTIALS_PATH=/absolute/private/path/service-account.json

Deploy and run: php artisan migrate --force && php artisan optimize:clear

## Delivery

SavedMakerShowNotificationService creates deduplicated in-app alerts for eligible saved-Maker/show pairs and calls SendPushNotification::dispatchAfterResponse only for newly created alerts. HTTP v1 delivery obtains Google OAuth 2.0 access tokens from the service account. Each message contains notification title/body plus notification ID, Maker ID, show ID and type data. Definite FCM UNREGISTERED responses clean up stale device records. Network/auth errors are logged without blocking show publishing.

The service currently runs after-response on shared hosting; a queue worker is not needed to get started. For high traffic, move delivery to a managed queue worker.

Existing eligibility rules remain: active Maker, visible current/upcoming show, active Appreciator who saved that Maker, opted-in notification setting, and one notification per recipient and show.

## Verification

Tests cover registration, validation, re-binding and unregistering user-owned device tokens. Real delivery requires Firebase console setup, physical devices, production server credentials and APNs setup for iOS.
