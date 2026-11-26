---
name: secure-development
description: Plan, implement and verify secure web applications from requirements using a general security checklist. Use for HelpdeskHub feature development and security review.
---
# Secure Development Skill

## Inputs
Read PRD.md, SECURITY-CHECKLIST.md, AGENTS.md and HANDOFF.md from the project root. Inspect actual code and environment rather than assuming their state.

## Procedure
1. Understand: identify actors, assets, operations, trust boundaries, data relationships and applicable security controls. Resolve ambiguous requirements with the human.
2. Plan: map features to routes, models, access policy, validation, business rules and tests. Record checklist IDs and planned evidence in IMPLEMENTATION-PLAN.md. Wait for approval before application coding.
3. Implement: work in dependency order (environment/schema, identity, core features, child resources, reporting). Use CodeIgniter filters, validation, Query Builder, escaping and session features appropriately. Centralize reusable security decisions where practical; ensure each operation invokes the appropriate policy. Use transactions for related changes. Never substitute frontend restrictions for server enforcement.
4. Verify: run applicable functional and security tests. Cover authorized and unauthorized behavior based on requirements. Check actual response and side effects. Review all implemented routes and dependencies. Do not weaken tests to achieve passing results.
5. Report: maintain SECURITY-REPORT.md with control ID, applicability, implementation evidence (file/line or symbol), verification command/test, result and limitations. Maintain TEST-REPORT.md with actual commands, counts, failures and skipped tests. Distinguish implementation from verification.
6. Handoff: update HANDOFF.md after each milestone and before ending a session. Record remaining tasks, blockers, decisions and next action. Do not store secrets.

## Completion criteria
Every required feature is accounted for, each applicable control has an honest status, legitimate workflows remain functional, and unresolved findings or blockers are explicit. Do not claim complete security. Independent instructor assessment is separate from AI self-verification.
