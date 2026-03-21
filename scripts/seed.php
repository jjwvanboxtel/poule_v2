<?php
// Simple seeder runner: runs all .sql files in database/seeders in order
$base = __DIR__ . '/../database';
$configFile = __DIR__ . '/../config.cfg.php';
function parseConfig($file) {
    $contents = file($file);
    $cfg = [];
    foreach ($contents as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '<?php') === 0) continue;
        if (preg_match('/^([A-Z0-9_]+)\s*=\s*(.*)$/', $line, $m)) {
            $key = $m[1];
            $val = trim($m[2]);
            $cfg[$key] = $val;
        }
    }
    return $cfg;
}
$cfg = parseConfig($configFile);
$host = $cfg['DB_HOST'] ?? '127.0.0.1';
$port = isset($cfg['DB_PORT']) ? (int)$cfg['DB_PORT'] : 3306;
$user = $cfg['DB_USERNAME'] ?? 'root';
$pass = $cfg['DB_PASSWORD'] ?? '';
$dbname = $cfg['DB_NAME'] ?? null;
if (!$dbname) { fwrite(STDERR, "DB_NAME missing in config.cfg.php\n"); exit(2); }
$mysqli = new mysqli($host, $user, $pass, $dbname, $port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Connect failed: " . $mysqli->connect_error . "\n");
    exit(2);
}
// Disable foreign key checks during seeding to avoid ordering issues
$mysqli->query('SET FOREIGN_KEY_CHECKS=0');
$seedDir = realpath(__DIR__ . '/../database/seeders');
if (!is_dir($seedDir)) { fwrite(STDERR, "No seeders directory found at $seedDir\n"); exit(1); }
$files = glob($seedDir . '/*.sql');
sort($files);
foreach ($files as $file) {
    echo "Running seeder: $file\n";
    $sql = file_get_contents($file);
    if ($sql === false) { echo "  cannot read\n"; continue; }
    if (!$mysqli->multi_query($sql)) {
        echo "  Error: " . $mysqli->error . "\n";
        break;
    }
    // consume all results
    do {
        if ($res = $mysqli->store_result()) { $res->free(); }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "  OK\n";
}
$mysqli->close();
echo "Seeders complete.\n";
