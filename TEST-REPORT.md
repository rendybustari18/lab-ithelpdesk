# Test Report

Status: **executed checks passed; coverage is partial.** Results below describe local training data only.

## Executed

| Check | Result |
|---|---|
| `docker compose up -d --build` | Passed. PHP 8.3 Apache and MySQL 8 started on `http://localhost:8081`; database health check passed. The app remains running. Host port 8080 was occupied, so Compose uses 8081. |
| CI4 migration status | Passed. Migrations 000001, 000002, and 000003 are applied to the persistent local training DB. |
| `docker compose exec -T app composer test` | Passed: 8 tests, 23 assertions (policy, attachment content, starter session/database tests). |
| `php -l` across `app/` and `tests/` PHP files | Passed after the final code changes. |
| `docker compose exec -T app php spark routes` | Passed. Explicit GET/POST routes were listed; auto-routing is disabled. |
| HTTP smoke: employee login, dashboard, ticket list, report | Passed; each returned 200. |
| HTTP authorization and CSRF | Passed for sampled cases: employee access to `/admin/users` returned 403; a login POST without CSRF token returned 403. |
| HTTP ticket lifecycle | Passed: employee created ticket 13; manager assigned it; IT Support commented and transitioned Open → In Progress → Resolved; employee closed it. |
| HTTP CSV export | Passed: employee export returned the created ticket within their scope. Formula-injection escaping was code-reviewed but not attacked with a dedicated fixture. |
| `docker compose exec -T app composer audit` | Passed: no security vulnerability advisories found for locked Composer dependencies at run time. |
| HTTP response headers | Inspected: CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, HttpOnly/SameSite cookies present; `X-Powered-By` absent. |
| Recovery after missing-table report | `migrate:status` showed all three migrations unapplied. Ran `php spark migrate`, then `php spark db:seed HelpdeskSeeder` (the users table was empty); login-rate table now exists and admin login/dashboard returned 200. No volume reset was performed. |

## Not verified

- Full role/resource matrix, inactive-account rejection, admin password reset/session invalidation, and horizontal IDOR checks were not exercised over HTTP.
- Attachment upload/download authorization and limits were not exercised over HTTP. Unit tests cover content signatures for PNG/JPEG/PDF policy.
- CSV formula escaping, rate-limit thresholds, concurrent update races, production HTTPS/cookie behavior, and backup/restore were not independently tested.
- The PRD's SLA monitoring has no defined target, calendar, pause rules, or metric; it is not implemented pending instructor clarification.

The current persistent local DB was empty of application schema when recovery began; migrations and training seed data were applied. No volume reset or destructive migration was run. Earlier workflow smoke data was in the prior database state and is not assumed to exist now.
