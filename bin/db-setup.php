<?php

/**
 * Database setup — creates the database, tables, and seeds default data.
 *
 * Usage:
 *   php bin/db-setup.php          (interactive)
 *   php bin/db-setup.php --force  (skip confirmation)
 *
 * Safe to run multiple times — uses CREATE IF NOT EXISTS and INSERT IGNORE.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MsDashboard\Config\Config;

$config = Config::load();
$force  = in_array('--force', $argv ?? [], true);

$host = $config->get('DB_HOST', 'localhost');
$port = $config->get('DB_PORT', '3306');
$user = $config->get('DB_USERNAME', 'root');
$pass = $config->get('DB_PASSWORD', '');
$name = $config->get('DB_DATABASE', 'login');

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  MS Daily Dashboard — Database Setup\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Host:     {$host}:{$port}\n";
echo "  User:     {$user}\n";
echo "  Database: {$name}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (!$force) {
    echo "This will create the database and tables if they don't exist,\n";
    echo "and seed default users (admin/admin, user/admin).\n\n";
    echo "Continue? [Y/n] ";
    $answer = trim(fgets(STDIN) ?: '');
    if ($answer !== '' && strtolower($answer) !== 'y') {
        echo "Aborted.\n";
        exit(0);
    }
    echo "\n";
}

// Step 1: Connect without database (to create it)
try {
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✓ Connected to MySQL\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: {$e->getMessage()}\n";
    exit(1);
}

// Step 2: Run schema (creates DB + tables)
$schemaFile = __DIR__ . '/../database/schema.sql';
if (!file_exists($schemaFile)) {
    echo "✗ Schema file not found: database/schema.sql\n";
    exit(1);
}

try {
    $schema = file_get_contents($schemaFile);
    $pdo->exec($schema);
    echo "✓ Database '{$name}' and tables created\n";
} catch (PDOException $e) {
    echo "✗ Schema error: {$e->getMessage()}\n";
    exit(1);
}

// Step 3: Switch to the database and seed
try {
    $pdo->exec("USE `{$name}`");

    // Only seed if user table is empty
    $count = (int) $pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
    if ($count === 0) {
        $seedFile = __DIR__ . '/../database/seed.sql';
        if (file_exists($seedFile)) {
            $seed = file_get_contents($seedFile);
            $pdo->exec($seed);
            echo "✓ Seeded default users (admin/admin, user/admin)\n";
        }
    } else {
        echo "✓ Users table already has {$count} row(s) — skipping seed\n";
    }
} catch (PDOException $e) {
    echo "✗ Seed error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  ✓ Setup complete! You can now run:\n";
echo "    make serve\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
