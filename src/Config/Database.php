<?php

declare(strict_types=1);

namespace MsDashboard\Config;

use PDO;
use PDOException;

/**
 * Database connection factory using .env configuration.
 */
final class Database
{
    private static ?PDO $connection = null;

    public static function connect(?Config $config = null): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = $config ?? Config::load();

        $host = $config->get('DB_HOST', 'localhost');
        $port = $config->get('DB_PORT', '3306');
        $name = $config->get('DB_DATABASE', 'login');
        $user = $config->get('DB_USERNAME', 'root');
        $pass = $config->get('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$connection = new PDO($dsn, $user, $pass, $opts);
        } catch (PDOException $e) {
            // Auto-create database + tables on first failure
            if (self::autoSetup($host, $port, $user, $pass, $name)) {
                try {
                    self::$connection = new PDO($dsn, $user, $pass, $opts);
                } catch (PDOException $retryEx) {
                    throw $config->debug() ? $retryEx : new \RuntimeException('Database connection failed after auto-setup.');
                }
            } else {
                throw $config->debug() ? $e : new \RuntimeException('Database connection failed.');
            }
        }

        return self::$connection;
    }

    public static function reset(): void
    {
        self::$connection = null;
    }

    /**
     * Auto-create the database, tables, and seed data if they don't exist.
     * Returns true on success so the caller can retry the connection.
     */
    private static function autoSetup(
        string $host,
        string $port,
        string $user,
        string $pass,
        string $name,
    ): bool {
        $schemaFile = dirname(__DIR__, 2) . '/database/schema.sql';
        $seedFile   = dirname(__DIR__, 2) . '/database/seed.sql';

        if (!file_exists($schemaFile)) {
            return false;
        }

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Create DB + tables
            $pdo->exec(file_get_contents($schemaFile));
            $pdo->exec("USE `{$name}`");

            // Seed if empty
            $count = (int) $pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
            if ($count === 0 && file_exists($seedFile)) {
                $pdo->exec(file_get_contents($seedFile));
            }

            return true;
        } catch (PDOException) {
            return false;
        }
    }
}
