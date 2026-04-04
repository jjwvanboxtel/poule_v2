# PHP Output & Template Security — Reference

Patterns for preventing XSS, template injection, sensitive data exposure, and insecure file handling in PHP output layers.

---

## Cross-Site Scripting (XSS)

### Risk Level: HIGH (stored) / MEDIUM (reflected)

### Vulnerable Patterns

```php
// CRITICAL: raw echo of user input into HTML
echo $_GET['search'];
echo "<p>Welcome, " . $_POST['name'] . "</p>";
echo "<script>var config = " . json_encode($user_data) . ";</script>";

// Template files
<title><?= $page_title ?></title>
<div class="username"><?= $user->name ?></div>
```

### Context-Aware Escaping

```php
// HTML context — most common
echo htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// HTML attribute context
echo htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
// e.g.: <input value="<?= htmlspecialchars($val, ENT_QUOTES) ?>">

// JavaScript context — encode for JS string literal
echo json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
// e.g.: var name = <?= json_encode($name, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

// URL context
echo urlencode($value);
// e.g.: <a href="/search?q=<?= urlencode($query) ?>">

// CSS context (avoid user input in CSS where possible)
// If unavoidable: allow-list values only
$color = preg_match('/^#[0-9a-f]{3,6}$/i', $_GET['color']) ? $_GET['color'] : '#000000';
```

### Helper Function (DRY)

```php
/**
 * Output-escape for HTML context.
 */
function e(mixed $value, string $encoding = 'UTF-8'): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, $encoding);
}

// Usage in templates
<p>Hello, <?= e($user_name) ?></p>
```

### Content Security Policy

```php
// Nonce-based CSP to allow inline scripts only with matching nonce
$nonce = base64_encode(random_bytes(16));
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}'; object-src 'none'");

// In template
<script nonce="<?= $nonce ?>">
    // This inline script is allowed
</script>
```

---

## Template / Server-Side Template Injection

### Risk Level: CRITICAL

### Vulnerable Patterns

```php
// Dynamic eval of template strings containing user input
eval('?>' . $template_from_db . '<?php ');

// Twig/Blade with user-controlled template names
$twig->render($_GET['template'] . '.html.twig', $data);

// Including user-controlled template paths
include 'templates/' . $_POST['theme'] . '/layout.php';
```

### Safe Patterns

```php
// Allow-list template identifiers
$allowed_templates = ['default', 'compact', 'print'];
$theme = in_array($_POST['theme'], $allowed_templates, true) ? $_POST['theme'] : 'default';
include __DIR__ . '/templates/' . $theme . '/layout.php';

// Realpath guard
$base = realpath(__DIR__ . '/templates');
$path = realpath($base . '/' . $theme . '/layout.php');
if ($path === false || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(403); exit;
}
include $path;

// Never pass user data as template source strings
// Use parameterised variables instead:
// SAFE:   $twig->render('user_profile.html.twig', ['name' => $user->name])
// UNSAFE: $twig->createTemplate($user_provided_string)->render($data)
```

---

## Sensitive Data Exposure in Output

### Common Leak Points

```php
// Verbose error messages exposing stack traces, file paths, DB credentials
echo $e->getMessage(); // may contain SQL with credentials

// Hidden form fields with internal IDs or hashed tokens
<input type="hidden" name="user_id" value="<?= $user['id'] ?>">

// JSON API responses including password hashes, tokens, or internal keys
echo json_encode($user);  // if $user contains password_hash, reset_token, etc.

// HTML comments with debug info
<!-- DB query: SELECT * FROM users WHERE id = 42 -->
```

### Safe Patterns

```php
// Generic error messages to users; detailed logs server-side
try {
    // ...
} catch (\Throwable $e) {
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'An internal error occurred.']);
}

// Explicit field allow-list for API responses
function user_to_api_array(array $user): array {
    return [
        'id'         => (int)$user['id'],
        'name'       => $user['name'],
        'email'      => $user['email'],
        'created_at' => $user['created_at'],
        // never include: password_hash, reset_token, internal flags
    ];
}
```

---

## File Upload Security

### Risk Level: CRITICAL → HIGH

### Vulnerable Patterns

```php
// Trusting client MIME type
if ($_FILES['upload']['type'] === 'image/jpeg') { // bypassable
    move_uploaded_file($_FILES['upload']['tmp_name'], 'uploads/' . $_FILES['upload']['name']);
}

// No filename sanitisation — path traversal possible
$dest = 'uploads/' . $_FILES['file']['name']; // could be "../../index.php"

// Upload directory inside webroot without execution prevention
```

### Secure Pattern

```php
function handle_upload(array $file, string $upload_dir): string {
    // 1. Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new \RuntimeException('Upload error: ' . $file['error']);
    }

    // 2. Verify it is an actual uploaded file
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new \RuntimeException('Not an uploaded file');
    }

    // 3. Check real MIME type via finfo (not client-supplied)
    $finfo    = new \finfo(FILEINFO_MIME_TYPE);
    $mime     = $finfo->file($file['tmp_name']);
    $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

    if (!array_key_exists($mime, $allowed)) {
        throw new \InvalidArgumentException("File type not allowed: {$mime}");
    }

    // 4. Enforce maximum file size
    $max_bytes = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $max_bytes) {
        throw new \InvalidArgumentException('File too large');
    }

    // 5. Generate a random, safe filename — never use original name
    $ext      = $allowed[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest     = rtrim($upload_dir, '/') . '/' . $filename;

    // 6. Move to a directory OUTSIDE webroot or with .htaccess blocking execution
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new \RuntimeException('Failed to move uploaded file');
    }

    return $filename;
}
```

### Upload Directory Hardening

```apache
# .htaccess in upload directory — deny direct execution
<FilesMatch "\.(php|php3|php5|phtml|phar|pl|py|cgi|sh)$">
    Deny from all
</FilesMatch>
Options -ExecCGI
AddType text/plain .php .phtml .phar
```

---

## Information Disclosure

### PHP Version Exposure

```php
// Remove X-Powered-By header
header_remove('X-Powered-By');
```

```ini
; php.ini
expose_php = Off
```

### Directory Listing

```apache
# .htaccess
Options -Indexes
```

### Config File Protection

```apache
# Deny direct access to config files
<FilesMatch "\.(cfg|ini|env|lock|json|md|sql)$">
    Deny from all
</FilesMatch>
```

### Debug Endpoints

```php
// NEVER expose in production
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    phpinfo();
}

// Ensure debug routes are removed or gated:
if ($_ENV['APP_ENV'] !== 'development') {
    http_response_code(404); exit;
}
```

---

## Email Header Injection

### Risk Level: MEDIUM → HIGH

### Vulnerable Pattern

```php
// Attacker injects \r\n to add BCC, CC, or extra headers
mail($_POST['email'], 'Welcome', $body);
```

### Safe Pattern

```php
// Validate email with filter
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if ($email === false) {
    throw new \InvalidArgumentException('Invalid email address');
}

// Strip CRLF from all header values
function sanitise_header(string $value): string {
    return preg_replace('/[\r\n\t]/', '', $value);
}

$subject = sanitise_header($_POST['subject']);
mail($email, $subject, $body, 'From: noreply@example.com');

// Better: use a library like PHPMailer or Symfony Mailer
// which handles header injection prevention internally
```
