# Milestone 15 — Security, Performance, Testing & Release Hardening

Milestone 15 closes the backend roadmap without introducing a competing architecture.

Key changes:

- Google/Apple OIDC identity-token verification using provider JWKS and RS256 signature validation, with cached keys and strict issuer/audience/expiry checks.
- Existing `social_accounts` foundation is reused; no duplicate identity table is introduced.
- Dedicated social-login and discovery rate limiters.
- Global defensive response headers and no-store caching for sensitive auth/account APIs.
- Explicit public Maker artwork-gallery endpoint so Maker Profile composition has a stable server contract.
- Release-readiness Artisan command with environment-safe and production-strict modes.
- Production-cache build verification (`config`, `routes`, `views`) in the verifier.
- Secret/tracked-env checks, migration/route checks, and redirect-safe real-server smoke tests.
- Scope/release documentation identifies provider-dependent and Flutter-dependent items rather than calling them locally complete.
