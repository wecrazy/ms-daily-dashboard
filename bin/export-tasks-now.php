<?php

/**
 * CLI command: Export real-time task data from Odoo.
 * Usage: php bin/export-tasks-now.php
 * Cron:  every-2-min cd /path/to/project && php bin/export-tasks-now.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MsDashboard\Config\Config;
use MsDashboard\Service\OdooExporter;

ini_set('memory_limit', '-1');
set_time_limit(0);

$config = Config::load();
date_default_timezone_set($config->timezone());

try {
    $exporter = new OdooExporter(config: $config);
    $message  = $exporter->exportTasksNow();
    echo $message . PHP_EOL;
} catch (Throwable $e) {
    fprintf(STDERR, "Error: %s\n", $e->getMessage());
    exit(1);
}
