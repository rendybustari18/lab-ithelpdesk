# HelpdeskHub

HelpdeskHub is a local training IT support portal built with CodeIgniter 4, PHP 8.3, MySQL 8, Apache, and Bootstrap 5.

## Run locally

Requirements: Docker Engine with Compose and an available host port 8081. The database and private uploads use persistent Docker volumes.

```sh
cp .env.example .env
docker compose up -d --build
```

Open [http://localhost:8081](http://localhost:8081). Compose runs migrations and adds training data on startup. It does not reset an existing database. The starter contains no production data.

All seeded accounts use the same **local training-only** password: `TrainingOnly!234`.

| Role | Email |
|---|---|
| Administrator | admin@example.test |
| IT Manager | manager@example.test |
| IT Support | support01@example.test, support02@example.test |
| Employee | employee01@example.test, employee02@example.test, employee03@example.test |

Change seeded passwords before using the app beyond a local workshop. Never use these credentials in production. `.env.example` also contains dummy-only database and encryption values. Replace them and set `CI_ENVIRONMENT=production`, HTTPS, and a fresh encryption key before any deployment. This project has not been approved for production use.

## Test and inspect

The Docker image includes PHPUnit for local verification:

```sh
docker compose exec app composer test
docker compose exec app php spark routes
```

Tests use CI4's isolated in-memory SQLite config; the application uses MySQL. Do not point automated tests at a database containing user data.

## Included features

- Role-scoped employee, IT Support, IT Manager, and Administrator experiences.
- Session login/logout, rate limiting, self-service profile/password, admin account management, password reset, category master data.
- Ticket CRUD, assignment history, workflow status changes, comments, private attachments, read-only audit history.
- Filtered and paginated ticket reports with CSV export and role-specific dashboard metrics/workload.
- Docker Compose, MySQL migrations, and training seeder.

## Known requirement pending

The PRD mentions SLA monitoring but does not define its target, calendar, pause behavior, or metric. No SLA calculation is implemented until the instructor defines those rules. See [IMPLEMENTATION-PLAN.md](IMPLEMENTATION-PLAN.md), [SECURITY-REPORT.md](SECURITY-REPORT.md), [TEST-REPORT.md](TEST-REPORT.md), and [HANDOFF.md](HANDOFF.md) for status and evidence.
# lab-ithelpdesk
