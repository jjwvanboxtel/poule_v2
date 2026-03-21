<!--
Sync Impact Report

Version change: TEMPLATE -> 0.1.0
Modified principles:
- None renamed; 5 principles added from repo spec
Added sections:
- Technical Constraints
- Development Workflow
Removed sections:
- None
Templates requiring updates:
- .specify/templates/plan-template.md ✅ updated
- .specify/templates/spec-template.md ⚠ pending
- .specify/templates/tasks-template.md ⚠ pending
Follow-up TODOs:
- None (RATIFICATION_DATE filled: 2026-03-21)
-->

# Voetbalpoule & Voorspelsysteem Constitution

## Core Principles

### Principle I: Test-First (NON-NEGOTIABLE)

All production-facing features MUST be specified with automated tests before implementation. Unit tests covering core logic and integration tests covering critical flows (predictions, scoring, standings) are required. Playwright end-to-end tests MUST validate the web UI for primary user journeys (join competition, submit predictions, view standings). Tests are the primary safety net for change; merges to main require green CI.

### Principle II: Minimal Shared Hosting Compatibility

The application MUST remain compatible with shared LAMP hosting constraints: avoid requiring privileged services, keep deployment steps file-based, and prefer server-side rendering. New dependencies that require additional server privileges or non-standard extensions MUST be justified in the PR and marked as OPTIONAL.

### Principle III: Security and Data Protection

All inputs MUST be validated and escaped server-side. CSRF protection MUST be present for all state-changing endpoints. Passwords and secrets MUST be stored using best-practice hashing/storing mechanisms. Sensitive configuration MUST not be committed to the repository; use config files outside webroot.

### Principle IV: Documentation-as-Code

Documentation MUST live under /docs in Markdown. Architectural diagrams should use mermaid. Quickstart, run instructions, and database schema MUST be documented. Every significant change that affects runtime or developer workflow MUST update docs.

### Principle V: Simplicity & Performance

Favor simple, auditable implementations over complex optimizations. Performance goals: responsive UI under typical shared-host limits; profile and optimize where real metrics show issues. Avoid premature micro-optimizations.

## Technical Constraints

- PHP 8.4 (target)
- MySQL (use existing poule_v2 database)
- Shared hosting (LAMP) compatible deployment
- Server-side rendering
- CSRF protection required

## Development Workflow

- Use TDD: write tests first
- Run unit + integration + Playwright tests

## Governance

All repository governance follows this constitution. Amendments MUST be proposed as a PR that updates this file, include a migration plan if the change affects runtime, and gather at least two maintainer approvals. Non-controversial wording fixes MAY be merged with a single maintainer approval.

**Version**: 0.1.0 | **Ratified**: 2026-03-21 | **Last Amended**: 2026-03-21
