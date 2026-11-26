# HelpdeskHub Implementation Plan

Status: **Approved 2026-11-26; approved implementation scope is built. Verification is partial; SLA remains unresolved.**

## Scope and source alignment

Implement every PRD module: authentication/profile, role-specific dashboard, user administration, ticket categories, tickets, assignments, comments, attachments, history, and filtered CSV reporting. Preserve BR-01 through BR-15 and the full authorization matrix. No instructor scope change is assumed.

The workspace currently contains source and training documents only; no application files or Git metadata were found. No application code or tests have been generated or run.

## Decisions needed before implementation

These points affect behavior and cannot be inferred safely from the PRD. Please decide or approve the recommendation for each:

1. **SLA:** PRD mentions manager SLA monitoring but defines no metric, target, calendar, pause rule, or display. Do not invent a calculation. SLA metrics remain blocked pending an instructor definition; the rest of the system can proceed.
2. **Ticket deletion:** Approved plan assumption: soft-delete an Employee's Open ticket and retain history and child records; hide it from normal views.
3. **Role changes with active assignments:** Approved plan assumption: block changing an assigned Support user's role until their Open/In Progress tickets are reassigned.
4. **Administrator password reset:** Approved plan assumption: admin provides a temporary password once; force change at next login and never log or redisplay the password.
5. **Runtime version:** Use PHP 8.3 per the user's project instructions. The available host CLI is 8.4.27; Docker will pin the application runtime to 8.3.

The user approved the plan and instructed implementation. The three lifecycle recommendations above are treated as approved design choices. SLA remains an explicit unresolved requirement and will not be represented as implemented.

## Architecture

- CodeIgniter 4 monolithic MVC, server-rendered views, Bootstrap 5, MySQL 8, PHP version as resolved above.
- Session authentication using CodeIgniter security mechanisms; explicit route definitions and filters, with legacy auto-routing disabled.
- Controllers handle HTTP, validation, and response formatting; models/Query Builder handle persistence. Keep authorization and workflow checks server-side and close to each operation; use transactions for related writes.
- Private attachments stored outside the public web root under generated names. Downloads pass through an authorized controller. No new framework or service is planned.
- Docker Compose provides the local app and MySQL environment; credentials are local dummy values from environment configuration, never production defaults.

## Routes

All routes are explicit. Public routes are limited to login and static assets. Authenticated routes require an active account; role and resource policy checks apply on every operation.

| Method and path | Access | Operation |
|---|---|---|
| GET/POST `/login`, POST `/logout` | Guest / authenticated | Sign in / sign out |
| GET/POST `/profile`, GET/POST `/profile/password` | Any active user | View/update own profile and password |
| GET `/` | Role-specific | Dashboard |
| GET `/tickets` | Employee, Support, Manager | Scoped list, search, filters, pagination |
| GET/POST `/tickets/new` | Employee | Create ticket |
| GET `/tickets/{id}` | Employee owner, assigned Support, Manager | Ticket detail and authorized children |
| GET/POST `/tickets/{id}/edit`, POST `/tickets/{id}/delete` | Owning Employee; Open only | Edit/delete ticket |
| POST `/tickets/{id}/status` | Assigned Support / owning Employee, permitted transitions only | Workflow status change |
| POST `/tickets/{id}/assignment` | Manager | Assign/reassign; assignment history |
| POST `/tickets/{id}/comments`, POST `/tickets/{id}/comments/{commentId}/edit`, POST `/tickets/{id}/comments/{commentId}/delete` | Ticket viewers; edit/delete own comment | Comment operations |
| POST `/tickets/{id}/attachments`, GET `/attachments/{id}`, POST `/attachments/{id}/delete` | Ticket viewers; delete own upload | Upload, download, delete |
| GET `/tickets/{id}/history` | Ticket viewers | Read-only history |
| GET `/reports`, GET `/reports/export.csv` | Employee, Support, Manager | Scoped report and CSV |
| GET `/admin/users`, GET/POST `/admin/users/new`, GET/POST `/admin/users/{id}/edit`, POST `/admin/users/{id}/reset-password`, POST `/admin/users/{id}/status` | Admin | User management |
| GET `/admin/categories`, POST `/admin/categories`, POST `/admin/categories/{id}/edit`, POST `/admin/categories/{id}/status` | Admin | Category management |

Mutation routes use POST (and CSRF protection); forms use framework validation and output escaping. Route names/verbs may be adjusted to the project’s CI4 conventions while preserving the explicit paths and policy.

## Data model

Use migrations, foreign keys, unique constraints, and indexes for lookup/filter columns. Exact migration syntax follows CI4 conventions.

- `users`: name, unique email, password hash, department, role allowlist, active flag, force-password-change flag, session version, timestamps.
- `categories`: name, active flag, timestamps. Tickets retain their category reference when a category is inactive.
- `tickets`: generated unique ticket number, subject, description, category, priority allowlist, status allowlist, creator, nullable current assignee, timestamps, optional soft-delete timestamp if decision 2 is approved.
- `ticket_assignments`: ticket, support user, assigning manager, assigned time; append-only history of assignment changes.
- `comments`: ticket, author, body, timestamps.
- `attachments`: ticket, uploader, generated storage key, original display name, detected MIME type, size, timestamps. No public path is stored or exposed.
- `ticket_histories`: ticket, actor, event type, safe event details, occurrence time; append-only and read-only to users.
- `login_rate_limits`: hashed account/IP key, 15-minute window start and atomic attempt count, to enforce concurrent login throttling.

Seed local training data only: seven specified dummy users, six categories, at least twelve tickets, twenty-four comments, twelve sample attachments, and history. Clearly document dummy credentials and prohibit their production use.

## Access policy and business rules

- Deny by default. Resolve the active user and current role from the server-side session/database on every protected request; inactive accounts cannot continue using an authenticated session.
- Employee ticket queries are constrained to creator; Support queries to current assignment; Manager queries to all tickets; Admin has no ticket access. Apply the same scopes to detail, comments, files, history, reports, exports, and dashboard queries.
- Only Employee creates tickets. BR-01 sets Open. Employee edit/delete requires ownership and Open (BR-02). Admin manages accounts/categories only (BR-11).
- Manager assigns only active IT Support users, and only while ticket is Open or In Progress (BR-03, BR-13). Record each assignment transactionally. Support handles only currently assigned tickets (BR-04).
- Enforce transitions: Support Open→In Progress→Resolved (BR-05/06); owning Employee may close or reopen a Resolved ticket; reopen becomes In Progress and keeps the same assignee (BR-07/14). Closed tickets cannot be modified, but remain readable by authorized users with comments/files (BR-08/15).
- Comment create/view follows ticket access; edit/delete requires comment ownership and current ticket access (BR-09). Attachment deletion requires uploader ownership and ticket access (BR-10); ticket status and file policy also apply.
- Profile edits are self-only; user/role/active/category administration is Admin-only. Role changes, password reset and ticket deletion follow the resolved decisions above.
- Use transaction/row-level consistency for status and assignment changes so history and current state cannot diverge. Prevent duplicate/invalid state transitions on the server.

## Validation and security controls

Apply all relevant controls from the 13 checklist categories; JWT-specific items are not applicable because authentication is session-based.

- Authentication/session: password hashing, uniform login failures, rate limiting, session ID regeneration, expiry, logout invalidation, secure cookie flags by environment, no public registration (SEC-01, SEC-03).
- Authorization: role/ownership/relationship checks on every route and child resource, active-account enforcement, deny-by-default, prevent horizontal/vertical access and stale privileges (SEC-02).
- Inputs/business logic: server validation for path/query/body, allowlisted enums, length/range and request-size limits, no mass assignment, legal transitions, transaction integrity and duplicate/race handling (SEC-04, SEC-06).
- Injection/output: Query Builder/parameter binding, context-aware escaping, CSRF tokens, path-safe generated attachment keys; avoid shell execution and unsafe deserialization (SEC-05, SEC-09).
- Files: allowlist JPG/PNG/PDF, verify extension plus detected MIME/content, 2 MB each, at most three per ticket, generated names, private storage, authorization before download/delete (SEC-07).
- Data/config/dependencies: hashes only, env-based secrets, least-privilege DB account, protected backups, production HTTPS/security headers/no debug, locked dependencies and vulnerability review (SEC-08, SEC-10, SEC-11).
- Logging/errors: generic user errors without stack/database details, no secrets in logs, log authentication/authorization failures and administrative actions, protect logs (SEC-12).
- Verification: review routes and controls, execute functional/security tests and dependency scan, document evidence and limitations (SEC-13).

## Milestones and verification

1. **Environment, CI4 skeleton, Docker, migrations and seeders.** Verify clean local startup, migration/seed execution and dummy data counts; verify secrets are not committed and production defaults are safe.
2. **Authentication, profile, user/category administration.** Test login/logout/password/profile, inactive-account rejection, role guards, validation, CSRF, and admin-only operations including reset behavior.
3. **Tickets, assignment, status lifecycle.** Test every BR-01–08, BR-13–14 transition and authorized/unauthorized owner, assignee, manager, admin access; check transactional history.
4. **Comments, attachments and history.** Test BR-09/10/15, file type/size/count limits, unauthorized download/delete, path traversal attempts, and append-only history.
5. **Dashboards, reports, CSV export, workload.** Test role-scoped metrics/rows, filters, pagination, CSV escaping, and admin account metrics. SLA remains gated by decision 1.
6. **Security and functional verification, documentation.** Run application test suite, checklist-driven route/access review, dependency scan and Docker smoke check. Record actual commands/results, findings and residual risks in `SECURITY-REPORT.md`, `TEST-REPORT.md`, `README.md`, and update this plan plus `HANDOFF.md`. Do not claim release readiness without instructor approval.

Tests should cover both permitted and denied requests, side effects after denied requests, and all documented workflows. No test suite has been run yet; the specific test commands will be selected after dependencies and runtime are available.

## Approval

Plan approval received 2026-11-26. Implementation proceeds under the decisions above; instructor input is still required to complete SLA monitoring.

## Implementation and verification snapshot

The approved application scope and required deliverables have been implemented. `docker compose up -d --build` starts the local app on port 8081 and applies all three migrations. PHPUnit passed (8 tests, 23 assertions); PHP lint, explicit route listing, selected HTTP role/CSRF/ticket lifecycle/CSV checks, and `composer audit` passed. Attachment policy unit tests passed, but HTTP attachment handling and several checklist cases remain unverified. See `TEST-REPORT.md` and `SECURITY-REPORT.md`. Do not treat this snapshot as approval for use beyond training.
