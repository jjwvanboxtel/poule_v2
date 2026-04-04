# PHP Injection Vulnerabilities — Reference

Detailed patterns, detection signals, and remediation for all injection attack classes in PHP.

---

## SQL Injection

### Risk Level: CRITICAL

### Vulnerable Patterns

```php
// String concatenation into query — CRITICAL
$result = $db->query("SELECT * FROM users WHERE email = '" . $_POST['email'] . "'");

// Untyped integer interpolation — HIGH
$result = mysqli_query($conn, "SELECT * FROM orders WHERE id = " . $_GET['id']);

// Second-order injection: data from DB used unsafely later
$username = $row['username']; // originally from untrusted input
$db->query("SELECT * FROM logs WHERE user = '$username'");
```

### Detection Search Patterns
```
grep -rn "query(" . --include="*.php" | grep -E '\$_(GET|POST|REQUEST|COOKIE|SERVER)'
grep -rn "\"SELECT\|INSERT\|UPDATE\|DELETE" . --include="*.php" | grep '\.'
```

### Safe Patterns — PDO Prepared Statements

```php
// Named placeholders
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND active = :active");
$stmt->execute([':email' => $email, ':active' => 1]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Positional placeholders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([(int)$_GET['id'], $_SESSION['user_id']]);
```

### Safe Patterns — mysqli

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password_hash = ?");
$stmt->bind_param("ss", $email, $hash);
$stmt->execute();
```

### Dynamic ORDER BY / Column Names (cannot be parameterised)

```php
// SAFE: allow-list column names
$allowed_columns = ['name', 'email', 'created_at'];
$sort = in_array($_GET['sort'], $allowed_columns) ? $_GET['sort'] : 'name';
$stmt = $pdo->query("SELECT * FROM users ORDER BY {$sort} ASC");
```

---

## Command Injection

### Risk Level: CRITICAL

### Vulnerable Patterns

```php
// Direct user input in shell command
exec("ping " . $_GET['host']);
shell_exec("convert " . $_FILES['image']['tmp_name'] . " output.png");
system("unzip " . $filename);

// Backtick operator (equivalent to shell_exec)
$output = `ls {$_GET['dir']}`;
```

### Detection Search Patterns
```
grep -rn "exec\|shell_exec\|system\|passthru\|popen\|proc_open" . --include="*.php"
grep -rn "escapeshellarg\|escapeshellcmd" . --include="*.php"   # check if used correctly
```

### Safe Patterns

```php
// Always escape arguments
$host = escapeshellarg($_GET['host']);
exec("ping -c 1 {$host}", $output, $return_code);

// Prefer built-in PHP functions over shell commands
// Instead of: exec("rm -rf " . $path)
// Use: unlink($path) or array_map('unlink', glob($path . '/*'))

// Validate input strictly before any shell use
if (!preg_match('/^[a-z0-9\-\.]{1,253}$/', $_GET['host'])) {
    throw new \InvalidArgumentException('Invalid hostname');
}
```

---

## File Path Traversal / Local File Inclusion (LFI)

### Risk Level: CRITICAL → HIGH

### Vulnerable Patterns

```php
// LFI — arbitrary file inclusion
include $_GET['page'] . '.php';
require $_REQUEST['template'];

// Path traversal in file read/write
$content = file_get_contents('/var/www/uploads/' . $_GET['file']);
$fp = fopen('/logs/' . $_POST['filename'], 'w');

// Null byte injection (PHP < 5.3.4)
include $_GET['page'] . '.php'; // ?page=../../etc/passwd%00
```

### Detection Search Patterns
```
grep -rn "include\|require" . --include="*.php" | grep -E '\$_(GET|POST|REQUEST)'
grep -rn "file_get_contents\|fopen\|readfile" . --include="*.php" | grep '\$_'
```

### Safe Patterns

```php
// Allow-list of permitted page identifiers
$allowed_pages = ['home', 'about', 'contact', 'dashboard'];
$page = $_GET['page'] ?? 'home';
if (!in_array($page, $allowed_pages, true)) {
    $page = 'home';
}
include __DIR__ . '/templates/' . $page . '.php';

// Realpath check to prevent traversal
$base  = realpath('/var/www/uploads');
$file  = realpath($base . '/' . basename($_GET['file']));
if ($file === false || strpos($file, $base) !== 0) {
    http_response_code(403); exit;
}
readfile($file);
```

---

## Header Injection / Open Redirect

### Risk Level: HIGH → MEDIUM

### Vulnerable Patterns

```php
// Open redirect
header('Location: ' . $_GET['return_url']);

// Header injection via CRLF
header('X-User: ' . $_GET['username']); // inject \r\n to add arbitrary headers
```

### Safe Patterns

```php
// Validate redirect URLs — allow same-origin only
$url = $_GET['return_url'] ?? '/dashboard';
$parsed = parse_url($url);
if (!empty($parsed['host']) && $parsed['host'] !== $_SERVER['HTTP_HOST']) {
    $url = '/dashboard'; // fallback to safe default
}
header('Location: ' . $url);

// Strip CRLF from header values
$username = preg_replace('/[\r\n]/', '', $_GET['username']);
header('X-User: ' . $username);
```

---

## XML/XXE Injection

### Risk Level: HIGH

### Vulnerable Patterns

```php
// External entity expansion enabled (default in older PHP)
$xml = simplexml_load_string($user_input);

// DOM parser with XXE
$doc = new DOMDocument();
$doc->loadXML($user_input);
```

### Safe Patterns

```php
// Disable external entities before parsing
libxml_disable_entity_loader(true); // PHP < 8.0
$xml = simplexml_load_string($user_input, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_NONET);

// PHP 8.0+: use LIBXML_NONET flag
$doc = new DOMDocument();
$doc->loadXML($user_input, LIBXML_NONET | LIBXML_NOENT);
```

---

## PHP Object Injection (Deserialization)

### Risk Level: CRITICAL

### Vulnerable Patterns

```php
// Any unserialize() on user data is CRITICAL
$prefs = unserialize($_COOKIE['preferences']);
$obj   = unserialize(base64_decode($_POST['data']));
$data  = unserialize(file_get_contents('php://input'));
```

### Detection Search Patterns
```
grep -rn "unserialize(" . --include="*.php"
```

### Safe Patterns

```php
// Use JSON instead — no object instantiation
$prefs = json_decode($_COOKIE['preferences'], true);
if (!is_array($prefs)) {
    $prefs = [];
}

// If unserialize is unavoidable (legacy): strict class allow-list (PHP 7.0+)
$obj = unserialize($data, ['allowed_classes' => [SafeClass::class]]);
```

---

## LDAP Injection

### Risk Level: HIGH

### Vulnerable Patterns

```php
$filter = "(&(uid=" . $_POST['username'] . ")(userPassword=" . $_POST['password'] . "))";
ldap_search($conn, $base_dn, $filter);
```

### Safe Patterns

```php
// Escape LDAP special characters
function ldap_escape_filter(string $value): string {
    $chars = ['\\', '*', '(', ')', "\x00"];
    $escaped = ['\\5c', '\\2a', '\\28', '\\29', '\\00'];
    return str_replace($chars, $escaped, $value);
}

$filter = sprintf(
    '(&(uid=%s)(objectClass=person))',
    ldap_escape_filter($_POST['username'])
);
```
