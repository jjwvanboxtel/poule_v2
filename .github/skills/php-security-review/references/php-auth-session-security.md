# PHP Authentication & Session Security — Reference

Patterns for secure authentication, session management, CSRF protection, and access control in PHP.

---

## Password Storage

### Risk Level: CRITICAL if broken

### Vulnerable Patterns

```php
// CRITICAL: reversible / fast hashes
$hash = md5($password);
$hash = sha1($password . $salt);
$hash = crypt($password);            // DES — deprecated
$hash = base64_encode($password);    // not a hash at all
```

### Secure Patterns

```php
// Preferred: Argon2id (PHP 7.3+)
$hash = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,  // 64 MB
    'time_cost'   => 4,
    'threads'     => 2,
]);

// Fallback: bcrypt (cost ≥ 12 in production)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verify
if (!password_verify($supplied_password, $stored_hash)) {
    // failed — no timing side-channel with password_verify
}

// Rehash if algorithm or cost changes
if (password_needs_rehash($hash, PASSWORD_ARGON2ID)) {
    $hash = password_hash($password, PASSWORD_ARGON2ID);
    // persist new hash
}
```

---

## Session Management

### Session Fixation

```php
// VULNERABLE: session ID not regenerated after login
session_start();
$_SESSION['user_id'] = $authenticated_user_id; // attacker knows the ID

// SECURE: regenerate ID on privilege change
session_start();
session_regenerate_id(true); // true = delete old session file
$_SESSION['user_id'] = $authenticated_user_id;
$_SESSION['ip']      = $_SERVER['REMOTE_ADDR'];
$_SESSION['ua']      = $_SERVER['HTTP_USER_AGENT'];
```

### Session Hijacking Prevention

```php
// Bind session to IP + UA fingerprint
function validate_session(): bool {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        return false;
    }
    // UA check (weaker but adds cost for attacker)
    if ($_SESSION['ua'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        session_destroy();
        return false;
    }
    return true;
}
```

### Secure php.ini Session Settings

```ini
; Prevent JavaScript access to session cookie
session.cookie_httponly = 1

; HTTPS only
session.cookie_secure = 1

; Strict SameSite to prevent CSRF via cookie
session.cookie_samesite = Strict

; Use cookies only (no session ID in URL)
session.use_only_cookies = 1
session.use_trans_sid    = 0

; Garbage collect old sessions
session.gc_maxlifetime = 3600
```

### Logout

```php
// SECURE: destroy session data and cookie on logout
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
```

---

## CSRF Protection

### Risk Level: HIGH for state-changing requests

### Vulnerable Pattern

```php
// No token — any site can trigger this POST
if ($_POST['action'] === 'delete_account') {
    $db->query("DELETE FROM users WHERE id = " . $_SESSION['user_id']);
}
```

### Secure Pattern — Synchronizer Token

```php
// Generate token on session start
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Embed in every form
echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';

// Validate on every state-changing request
function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('CSRF validation failed');
    }
}
```

### Double-Submit Cookie Pattern (stateless APIs)

```php
// Set cookie on page load
$token = bin2hex(random_bytes(32));
setcookie('csrf', $token, ['samesite' => 'Strict', 'secure' => true, 'httponly' => false]);

// Validate: cookie value must match header value
$cookie_token  = $_COOKIE['csrf'] ?? '';
$header_token  = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!hash_equals($cookie_token, $header_token) || empty($cookie_token)) {
    http_response_code(403); exit;
}
```

---

## Broken Access Control

### Direct Object Reference

```php
// VULNERABLE: user can enumerate other users' data
$order = $db->query("SELECT * FROM orders WHERE id = " . (int)$_GET['id']);

// SECURE: scope query to authenticated user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([(int)$_GET['id'], (int)$_SESSION['user_id']]);
```

### Role-Based Access Control

```php
// Centralised permission check — call at top of every privileged handler
function require_role(string ...$roles): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login'); exit;
    }
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403); exit('Forbidden');
    }
}

// Usage
require_role('admin', 'moderator');
```

---

## Password Reset Flow

### Vulnerable Pattern

```php
// Predictable token (guessable)
$token = md5($email . time());

// Token stored in plain text — DB breach exposes all reset tokens
$db->query("UPDATE users SET reset_token = '$token' WHERE email = '$email'");
```

### Secure Pattern

```php
// Cryptographically random, stored as hash
$raw_token    = bin2hex(random_bytes(32));
$hashed_token = hash('sha256', $raw_token);
$expiry       = date('Y-m-d H:i:s', strtotime('+30 minutes'));

$stmt = $pdo->prepare(
    "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?"
);
$stmt->execute([$hashed_token, $expiry, $email]);

// Send $raw_token in email — never $hashed_token

// Verify on submission
$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()"
);
$stmt->execute([hash('sha256', $_GET['token'])]);
$user = $stmt->fetch();

if (!$user) {
    // Invalid or expired
}

// Invalidate after use
$pdo->prepare("UPDATE users SET reset_token = NULL, reset_expiry = NULL WHERE id = ?")
    ->execute([$user['id']]);
```

---

## Brute-Force Protection

```php
// Simple rate limiting with DB/cache
function check_login_attempts(string $ip, string $username): void {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE (ip = ? OR username = ?) AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
    );
    $stmt->execute([$ip, $username]);
    if ($stmt->fetchColumn() >= 5) {
        http_response_code(429);
        exit('Too many attempts. Try again in 15 minutes.');
    }
}

function record_login_attempt(string $ip, string $username, bool $success): void {
    $pdo->prepare("INSERT INTO login_attempts (ip, username, success, attempted_at) VALUES (?,?,?,NOW())")
        ->execute([$ip, $username, (int)$success]);
}
```

---

## Secure Cookie Flags

```php
// Setting cookies with all security flags (PHP 7.3+)
setcookie('session_token', $value, [
    'expires'  => time() + 3600,
    'path'     => '/',
    'domain'   => 'example.com',
    'secure'   => true,       // HTTPS only
    'httponly' => true,       // No JS access
    'samesite' => 'Strict',   // CSRF mitigation
]);
```

---

## Security Headers Checklist

```php
// Add to every response (ideally in a front-controller or middleware)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'");
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
// HTTPS only:
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
```
