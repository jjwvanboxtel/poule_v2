# OWASP Top 10 for PHP — Reference

Mapping of OWASP Top 10 (2021) to PHP-specific vulnerability patterns, detection signals, and remediation guidance.

---

## A01:2021 — Broken Access Control

### PHP-Specific Patterns
- Direct object reference via `$_GET['id']` without ownership check
- Role/permission checks skipped for AJAX endpoints
- Forcing browse to admin URLs (no middleware/guard on routes)
- `VALID_ACCESS` constant guard bypassed if `index.php` is not the sole entry

### Detection Signals
```php
// RISK: no authorisation check before fetching another user's data
$user = $db->query("SELECT * FROM users WHERE id = " . $_GET['id']);

// SAFE: verify ownership
if ($_SESSION['user_id'] !== (int)$_GET['id'] && !hasRole('admin')) {
    http_response_code(403); exit;
}
```

### Remediation
- Centralise authorisation in a guard/middleware called on every request
- Never trust client-supplied IDs — verify ownership server-side
- Use allow-lists for permitted routes per role

---

## A02:2021 — Cryptographic Failures

### PHP-Specific Patterns
- `md5()` / `sha1()` for password storage
- `base64_encode()` mistaken for encryption
- Hard-coded secrets in source files
- `openssl_encrypt()` used with ECB mode or static IVs
- Sensitive data in GET parameters (visible in logs/referrer headers)

### Detection Signals
```php
// CRITICAL: reversible hash, rainbow-table vulnerable
$hash = md5($password);

// SAFE: adaptive hash with cost factor
$hash = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4]);
```

### Remediation
- Use `password_hash()` + `password_verify()` exclusively for passwords
- Generate tokens with `random_bytes(32)` → `bin2hex()`
- Store secrets in `.env`, never commit to VCS
- Enforce TLS; set `Strict-Transport-Security` header

---

## A03:2021 — Injection

See `php-injection-vulnerabilities.md` for full detail.

### Quick Reference
| Type | Dangerous Functions |
|------|---------------------|
| SQL | Raw string concat into `query()` / `mysqli_query()` |
| Command | `exec`, `shell_exec`, `system`, `passthru`, backtick operator |
| LDAP | `ldap_search()` with unsanitised input |
| Header | `header('Location: ' . $_GET['url'])` |
| File | `include $_GET['page']` |

---

## A04:2021 — Insecure Design

### PHP-Specific Patterns
- No rate limiting on login endpoints
- Password reset tokens not expiring
- Security questions as sole MFA
- Business logic that trusts client-side price/quantity values

### Remediation
- Implement account lockout after N failed logins
- Password reset tokens: single-use, expire in 15–60 minutes, stored as hashed value
- Validate all business-critical values server-side

---

## A05:2021 — Security Misconfiguration

### PHP-Specific Patterns
```ini
; RISKY in production
display_errors = On
expose_php = On
allow_url_include = On
register_globals = On   ; PHP < 5.4, still seen in legacy apps
```

### Detection Signals
- `phpinfo()` accessible publicly
- `.env` or `config.cfg.php` inside webroot without protection
- Default error handler revealing stack traces to users
- Directory listing enabled on upload folders

### Remediation
```ini
; php.ini — production settings
display_errors = Off
log_errors = On
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
```

---

## A06:2021 — Vulnerable and Outdated Components

### PHP-Specific Patterns
- Outdated Composer dependencies (`composer outdated`)
- Bundled third-party libraries not tracked (copy-paste JS/PHP libs)
- PHP version itself end-of-life

### Remediation
```bash
composer audit          # check for known CVEs
composer outdated       # list stale packages
```
- Subscribe to PHP security announcements
- Pin and audit all third-party includes

---

## A07:2021 — Identification and Authentication Failures

See `php-auth-session-security.md` for full detail.

### Quick Reference
- Session fixation: call `session_regenerate_id(true)` post-login
- No brute-force protection on login
- "Remember me" tokens stored in plain text in DB
- Weak password policy (no minimum length/complexity)

---

## A08:2021 — Software and Data Integrity Failures

### PHP-Specific Patterns
- `unserialize()` on user-controlled data → PHP Object Injection
- Auto-loading gadget chains enable RCE
- No integrity check on downloaded/included remote files

### Detection Signals
```php
// CRITICAL: arbitrary object instantiation
$obj = unserialize($_COOKIE['data']);

// SAFE: use JSON for serialising non-object data
$data = json_decode($_COOKIE['data'], true);
```

### Remediation
- Never call `unserialize()` on untrusted input
- Use JSON or message packs for cross-boundary data
- Verify Composer package checksums (`composer.lock`)

---

## A09:2021 — Security Logging and Monitoring Failures

### PHP-Specific Patterns
- Failed login attempts not logged
- No audit trail for privileged actions (user deletion, role change)
- Logs written to webroot (accessible via HTTP)
- Log injection via unescaped user input in log strings

### Remediation
```php
// Log failed login with context, sanitised
error_log(sprintf(
    '[AUTH_FAIL] user=%s ip=%s ua=%s',
    preg_replace('/[^\w@.-]/', '', $username),
    $_SERVER['REMOTE_ADDR'],
    substr(strip_tags($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 200)
));
```

---

## A10:2021 — Server-Side Request Forgery (SSRF)

### PHP-Specific Patterns
- `file_get_contents($_GET['url'])` or `curl_setopt($ch, CURLOPT_URL, $_GET['url'])`
- Webhook/callback URLs accepted from user without allow-listing
- XML/SVG uploads parsed with external entity resolution enabled

### Detection Signals
```php
// CRITICAL: arbitrary URL fetch
$data = file_get_contents($_POST['webhook_url']);

// SAFE: allow-list permitted hosts
$allowed = ['https://api.trusted.com'];
if (!in_array(parse_url($_POST['url'], PHP_URL_HOST), $allowed_hosts)) {
    throw new \InvalidArgumentException('URL not allowed');
}
```

### Remediation
- Allow-list permitted external hosts
- Disable `allow_url_fopen` and use curl with explicit host validation
- Disable XXE: `libxml_disable_entity_loader(true)` (PHP < 8.0) / use `LIBXML_NONET`
