---
name: php-security-review
description: Toolkit for auditing PHP codebases for security vulnerabilities. Use when asked to perform a security review, security audit, vulnerability scan, penetration test prep, or OWASP assessment on PHP applications. Covers SQL injection, XSS, CSRF, broken authentication, session hijacking, insecure deserialization, file upload risks, command injection, directory traversal, and insecure cryptography. Produces a structured security report with severity ratings and remediation guidance.
license: Complete terms in LICENSE.txt
metadata:
  version: "1.0.0"
  domain: security
  triggers: security review, security audit, PHP vulnerability, OWASP, SQL injection, XSS, CSRF, penetration test, pentest prep, insecure code, security scan
  role: auditor
  scope: review
  output-format: report
  related-skills: php-pro
---

# PHP Security Review

Expert security auditor for PHP codebases. Identifies vulnerabilities mapped to OWASP Top 10 and PHP-specific attack surfaces, then produces a prioritised remediation report.

## When to Use This Skill

- "Run a security review on this PHP codebase"
- "Check this code for SQL injection / XSS / CSRF vulnerabilities"
- "Audit authentication and session handling in PHP"
- "Scan for insecure file uploads or command injection"
- "Prepare a security report before deployment"
- "Find OWASP Top 10 issues in my PHP app"

## Prerequisites

- PHP source files accessible (modules, controllers, templates, config)
- Knowledge of the entry point (e.g., `index.php`, public router)
- Access to database query patterns and user-input handling code

## Reference Guide

Load detailed guidance based on the vulnerability category being investigated:

| Topic | Reference | Load When |
|-------|-----------|-----------|
| OWASP Top 10 for PHP | `references/owasp-top10-php.md` | Full audit or OWASP-specific review |
| Injection Vulnerabilities | `references/php-injection-vulnerabilities.md` | SQL, command, LDAP, header injection |
| Auth & Session Security | `references/php-auth-session-security.md` | Login, sessions, CSRF, password handling |
| Output & Template Security | `references/php-output-security.md` | XSS, template injection, file exposure |

## Step-by-Step Audit Workflow

### Phase 1 — Reconnaissance

1. Identify PHP version (check `phpinfo()` calls, `composer.json`, or server config)
2. Map entry points: `index.php`, routing files, API endpoints, AJAX handlers
3. Identify user-controlled input sources: `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_FILES`, `$_SERVER`
4. Locate database interaction code (raw PDO/mysqli, query builders, ORMs)
5. Find authentication and session management code
6. Identify file upload and filesystem operation code

### Phase 2 — Injection Scanning

Load [`references/php-injection-vulnerabilities.md`](./references/php-injection-vulnerabilities.md) and scan for:

- SQL injection: unparameterised queries with user input
- Command injection: `exec()`, `shell_exec()`, `system()`, `passthru()` with user data
- Header injection: `header()` calls with unsanitised values
- File path traversal: `include()`, `require()`, `fopen()`, `file_get_contents()` with user input
- LDAP/XML injection patterns

### Phase 3 — Authentication & Session Review

Load [`references/php-auth-session-security.md`](./references/php-auth-session-security.md) and check:

- Password storage: `password_hash()` with `PASSWORD_BCRYPT` or `PASSWORD_ARGON2ID`
- Session fixation: `session_regenerate_id(true)` called after login
- CSRF tokens: presence on all state-changing forms and AJAX calls
- Broken access control: direct object references, missing authorisation checks
- Credential exposure in logs, error messages, or HTML comments

### Phase 4 — Output & Template Security

Load [`references/php-output-security.md`](./references/php-output-security.md) and check:

- XSS: unescaped output in HTML context (`echo $var` without `htmlspecialchars()`)
- Reflected XSS in error messages and search results
- Stored XSS in database-persisted values echoed to templates
- Template file inclusion with user-controlled paths
- Sensitive data exposure in responses (tokens, hashes, PII)

### Phase 5 — Cryptography & Configuration Review

Check against OWASP A02 and A05:

- Weak hashing: `md5()`, `sha1()` used for passwords or tokens
- Hard-coded secrets in source (API keys, DB passwords, salts)
- Insecure random: `rand()`, `mt_rand()` used for security tokens
- Missing HTTPS enforcement, insecure cookie flags (`secure`, `httponly`, `samesite`)
- `error_reporting(E_ALL)` or `display_errors=1` in production config

### Phase 6 — File Upload & Deserialization

- File type validation: MIME-type-only checks (bypassable) vs. extension + content validation
- Upload directory inside webroot (direct execution risk)
- `unserialize()` with user-controlled input (PHP object injection)
- Unsafe use of `eval()` or `preg_replace()` with `e` modifier (deprecated but still found)

### Phase 7 — Report Generation

Produce a structured **Security Audit Report** using the output format below.

## Output Format — Security Audit Report

```
# PHP Security Audit Report

**Date:** YYYY-MM-DD
**Scope:** [files / modules reviewed]
**Auditor:** PHP Security Review Skill v1.0.0

---

## Executive Summary
[2–3 sentence overview: severity distribution, most critical issues, overall risk posture]

## Findings

### [VULN-001] [Severity] — [Vulnerability Name]
- **Location:** `path/to/file.php:line`
- **OWASP Category:** A0X:202X – [Name]
- **Description:** [What the vulnerability is and how it can be exploited]
- **Evidence:** [Code snippet showing the issue]
- **Remediation:** [Specific fix with code example]
- **References:** [CWE / OWASP link]

### [VULN-002] ...

---

## Summary Table

| ID | Severity | Category | File | Status |
|----|----------|----------|------|--------|
| VULN-001 | CRITICAL | SQL Injection | modules/database.class.php | Open |

---

## Remediation Priority

1. **Immediate (CRITICAL):** [list]
2. **Short-term (HIGH):** [list]
3. **Planned (MEDIUM):** [list]
4. **Backlog (LOW/INFO):** [list]
```

## Severity Scale

| Level | Criteria |
|-------|----------|
| **CRITICAL** | Direct RCE, authentication bypass, full data exfiltration possible |
| **HIGH** | SQL injection, stored XSS, CSRF on privileged actions, broken auth |
| **MEDIUM** | Reflected XSS, IDOR, weak crypto, missing security headers |
| **LOW** | Information disclosure, verbose errors, minor misconfigurations |
| **INFO** | Best-practice deviations, hardening recommendations |

## Troubleshooting

| Issue | Resolution |
|-------|------------|
| No entry point found | Search for `$_GET`, `$_POST` references; trace from webroot `index.php` |
| Large codebase | Prioritise user-input handling files first, then auth, then templates |
| Legacy PHP (< 7.4) | Flag PHP version itself as HIGH; check for removed dangerous functions |
| Framework detected | Load matching reference (Laravel/Symfony) alongside this skill |
