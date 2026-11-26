# Security Report

Status: implementation evidence recorded; verification remains partial. This report is not a certification or production approval.

| Checklist | Status | Implementation and limitations |
|---|---|---|
| SEC-01 Authentication | Implemented; partially verified | Password hashes and generic errors; atomic per-IP/account 15-minute rate limit; session regeneration; active-account recheck; forced password change after admin reset. HTTP login passed. Inactive accounts, reset flow, and rate threshold were not exercised. |
| SEC-02 Access control | Implemented; partially verified | Explicit routes, role filter, `TicketPolicy`, ownership/assignment checks on parent and child resources, DB role refreshed per request. Employee admin denial returned 403; policy unit tests passed; full resource matrix/IDOR checks remain. |
| SEC-03 Session management | Implemented; partially verified | CI4 server-side sessions; two-hour expiry; regenerate/destroy on login/logout; HttpOnly and SameSite=Lax. Response cookie inspected. Production Secure-cookie and invalidation behavior were not separately exercised. |
| SEC-04 Input validation | Implemented; code-reviewed | Server validation, role/status/priority allowlists, bounded fields, scalar request helpers, bounded dates and pagination. Adversarial payload coverage remains limited. |
| SEC-05 Injection prevention | Implemented; code-reviewed | CI4 Query Builder/bound queries and escaped views; CSV output escaping; generated private-file keys and strict download key validation. SQL/XSS attack fixtures were not run. |
| SEC-06 Business logic | Implemented; workflow partially verified | Server-side ticket transitions, transactions and row locks. Manager assignment, support comment/status progression, and employee closure passed over HTTP. Concurrency races were not simulated. SLA rules remain unspecified by the PRD. |
| SEC-07 File handling | Implemented; policy unit-tested | Private `writable/uploads`, generated storage keys, extension/MIME/content checks, 2 MB and three-file limits, scoped downloads and uploader-only delete. Signature unit tests passed; HTTP upload/download and race cases remain unverified. |
| SEC-08 Data protection | Implemented for training | Password hashes; private files; DB/encryption settings supplied through environment; Compose values are dummy training credentials. Backup/restore and deployment secrets were not reviewed. |
| SEC-09 Web security | Implemented; sampled checks passed | CSRF is applied to routes; missing-token login POST returned 403. CSP, frame/content/referrer headers and cookie flags inspected. Same-origin redirect helper is used for back navigation; no automated hostile-Referer integration test. |
| SEC-10 Configuration | Implemented for local training | Apache document root is `public/`; debug toolbar disabled; production DB errors disabled. Compose defaults to development and must not be exposed publicly. |
| SEC-11 Supply chain | Verified for Composer lock | CI4 4.7.6 and dependencies locked; `composer audit` reported no advisories. Bootstrap 5.3.9 is version-pinned with SRI. |
| SEC-12 Logging/errors | Implemented; partially reviewed | Login/authorization and admin actions logged without passwords or email addresses; CI4 production exception handling. Log retention/access settings require deployment review. |
| SEC-13 Verification | Partial | See `TEST-REPORT.md`: 8 tests / 23 assertions, PHP lint, migrations, routes, selected HTTP workflows, sampled headers, and Composer audit passed. Full checklist coverage remains incomplete. |

## Known unresolved item

The PRD mentions SLA monitoring but defines no target, clock/calendar, pause behavior, or metric. No SLA feature is claimed. Instructor definition is required before it can be implemented and verified.

## Readiness

The application is a training build. Do not use beyond local training until remaining verification is complete, the SLA requirement is resolved, deployment secrets/HTTPS are configured, and an instructor approves readiness.
