# PROJECT: HelpdeskHub

## Role

Act as a Senior Software Engineer with expertise in CodeIgniter 4, PHP, MySQL, and secure software development.

## Objective

Build a fully functional internal IT Helpdesk Management System from scratch.

Security is a mandatory application requirement, not an optional enhancement.

## Project References

Read and understand the following documents before starting development:

PRD.md (includes business requirements and authorization matrix)

AGENTS.md

HANDOFF.md

.agents/skills/secure-development/SKILL.md

SECURITY-CHECKLIST.md

All requirements in these documents must be considered during application design and implementation.

## Technology Stack

Monolithic MVC architecture

CodeIgniter 4

PHP 8.3

MySQL 8

Bootstrap 5

HTML, CSS, and JavaScript

Session-based authentication

Apache

Docker Compose

Do not introduce additional frameworks or unnecessary services.

## Development Requirements

Implement all application features defined in the business requirements.

Follow the authorization matrix and business rules.

Implement all applicable security requirements.

Use maintainable and readable source code.

Use secure framework features appropriately.

Provide database migrations and seeders.

Provide dummy users and application data.

Provide a responsive and consistent user interface.

Ensure the application can run using Docker Compose.

Provide application documentation.

## Development Workflow

### Phase 1 — Planning

Analyze all project requirements.

Prepare:

Application architecture

Database schema

Application routes

Feature implementation plan

Security implementation plan

Testing strategy

Do not generate application source code until the implementation plan has been approved.

### Phase 2 — Implementation

After approval, implement the application according to the approved plan.

Follow the security checklist throughout the development process.

Do not intentionally introduce vulnerabilities.

Do not disable security controls to resolve implementation errors.

Do not change business requirements without approval.

### Phase 3 — Verification

Review the implemented application against:

Business requirements

Authorization matrix

Security checklist

Execute applicable functional and security tests.

Document the actual test results.

Do not claim that a security requirement has passed without sufficient evidence.

## Deliverables

Complete application source code

Dockerfile

docker-compose.yml

.env.example

Database migrations

Database seeders

README.md

IMPLEMENTATION-PLAN.md

SECURITY-REPORT.md

TEST-REPORT.md

The README must include installation instructions, application URL, dummy account credentials, and instructions for running tests.

The application must be runnable locally using:

docker compose up -d --build

## Definition of Done

Application runs successfully.

Required features are implemented.

Business rules are enforced.

Applicable security requirements are implemented.

Dummy data is available.

Functional and security tests are executed.

Known limitations and unverified requirements are documented.

Start with Phase 1 — Planning.

## Workshop execution
Target 2–3 hours; report blockers early. Do not silently defer required features. Update HANDOFF.md after each milestone.

## Approval prompt
The implementation plan is approved. Implement according to the PRD, checklist, rules and skill. Run tests and update HANDOFF.md.

## Resume prompt
Read HANDOFF.md and all referenced documents, inspect actual workspace state, and continue the next approved incomplete task.
