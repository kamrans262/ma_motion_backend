# MA Motion API Foundation

## Versioning

All Flutter-facing application endpoints live under `/api/v1`.

The version is part of the public contract so future API revisions can coexist without silently breaking released mobile clients.

## Authentication

Laravel Sanctum is the token-authentication foundation for the Flutter client. Authentication endpoints and token lifecycle rules are implemented in the authentication milestone; this milestone only enables the `HasApiTokens` capability and verifies token issuance.

## Response Contract

Successful responses use this top-level shape:

```json
{
  "success": true,
  "message": "...",
  "data": {},
  "errors": null,
  "meta": null
}
```

Error responses use the same stable envelope:

```json
{
  "success": false,
  "message": "...",
  "data": null,
  "errors": null,
  "meta": null
}
```

Validation failures populate `errors`. Pagination and collection metadata may populate `meta` later without changing the top-level contract.

## Architecture Rules

- Controllers stay thin and presentation/transport focused.
- Business rules belong in feature Actions/Services and related domain/application code when justified.
- API resources/serializers will shield clients from raw database model structures.
- API errors must not expose stack traces, SQL errors, secrets, or internal exception details.
- New mobile endpoints must be versioned and tested.
- The Laravel backend remains authoritative for validation, authorization, ownership, and other sensitive rules.
