<?php
// Generate migration (schema) and seeder (data) SQL files from database/poule.sql
$base = __DIR__ . '/../database';
$dump = $base . '/poule.sql';
$migDir = $base . '/migrations';
$seedDir = $base . '/seeders';
if (!is_readable($dump)) {
    fwrite(STDERR, "Cannot read $dump\n");
    exit(2);
}
@mkdir($migDir, 0755, true);
@mkdir($seedDir, 0755, true);
$lines = file($dump);
$schema = [];
$seed = [];
foreach ($lines as $line) {
    $trim = ltrim($line);
    // skip the CREATE DATABASE / USE lines
    if (stripos($trim, 'CREATE DATABASE') === 0 || stripos($trim, 'USE ') === 0) {
        continue;
    }
    // collect INSERT lines into seed file
    if (stripos($trim, 'INSERT INTO') === 0) {
        $seed[] = $line;
        continue;
    }
    // otherwise keep for schema (including CREATE TABLE, indexes, comments)
    $schema[] = $line;
}
$migFile = realpath($migDir) . '/001_poule_schema.sql';
$seedFile = realpath($seedDir) . '/001_poule_seed.sql';
file_put_contents($migFile, implode('', $schema));
file_put_contents($seedFile, implode('', $seed));
echo "Wrote:\n  $migFile\n  $seedFile\n";
echo "\nUsage:\n  php scripts/migrate.php    # runs migrations\n  php scripts/seed.php       # runs seeders\n";
