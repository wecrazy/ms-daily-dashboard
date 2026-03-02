<?php

declare(strict_types=1);

namespace MsDashboard\Service;

use MsDashboard\Config\Config;

/**
 * Reads RESULT_{date}.txt / RESULTNow_{date}.txt log files and produces
 * chart-ready data arrays for Highcharts (weekly stacked columns).
 *
 * Replaces: controllers/last_week_json.php, running_week_json.php, running_week_json_now.php
 */
final readonly class TaskDataService
{
    private string $logPath;

    public function __construct(?Config $config = null)
    {
        $config = $config ?? Config::load();
        $this->logPath = $config->logPath();
    }

    /**
     * Get last week's chart data for a company.
     *
     * @return array{chartData: list<array>, monday: string, sunday: string}
     */
    public function getLastWeekData(string $companyName): array
    {
        $date = date('Y-m-d', strtotime('-7 days'));

        [$monday, $sunday] = $this->getWeekBounds($date);

        $dateCounts = $this->aggregateDateRange($monday, $sunday, $companyName, 'RESULT_');

        return [
            'chartData' => $this->buildChartData($dateCounts, $monday, $sunday),
            'monday'    => $monday,
            'sunday'    => $sunday,
        ];
    }

    /**
     * Get current week's chart data for a company (scheduled data).
     *
     * @return array{chartData: list<array>, monday: string, sunday: string}
     */
    public function getRunningWeekData(string $companyName): array
    {
        $date = date('Y-m-d');

        [$monday, $sunday] = $this->getWeekBounds($date);

        $dateCounts = $this->aggregateDateRange($monday, $date, $companyName, 'RESULT_');

        return [
            'chartData' => $this->buildChartData($dateCounts, $monday, $sunday),
            'monday'    => $monday,
            'sunday'    => $sunday,
        ];
    }

    /**
     * Get current week's real-time data for a company (RESULTNow files).
     *
     * @return array{chartData: list<array>, monday: string, sunday: string, suffix: string}
     */
    public function getRunningWeekNowData(string $companyName): array
    {
        $date = date('Y-m-d');

        [$monday, $sunday] = $this->getWeekBounds($date);

        $dateCounts = $this->aggregateDateRange($monday, $date, $companyName, 'RESULTNow_');

        // Use _now suffix for real-time keys
        $chartData = $this->buildChartData($dateCounts, $monday, $sunday, '_now');

        return [
            'chartData' => $chartData,
            'monday'    => $monday,
            'sunday'    => $sunday,
            'suffix'    => '_now',
        ];
    }

    /**
     * Calculate Monday and Sunday for the week containing $date.
     *
     * @return array{0: string, 1: string}
     */
    private function getWeekBounds(string $date): array
    {
        $dayOfWeek = (int) date('N', strtotime($date));

        $daysUntilMonday = $dayOfWeek - 1;
        $monday = $daysUntilMonday > 0
            ? date('Y-m-d', strtotime("-{$daysUntilMonday} days", strtotime($date)))
            : $date;

        $daysUntilSunday = 7 - $dayOfWeek;
        $sunday = $daysUntilSunday > 0
            ? date('Y-m-d', strtotime("+{$daysUntilSunday} days", strtotime($date)))
            : $date;

        return [$monday, $sunday];
    }

    /**
     * Read log files for a date range, aggregate stage/visit counts per date
     * filtered by company name.
     *
     * @return array<string, array{berhasil: int, gagal: int, open_pending: int, verified: int, visited: int, not_visit: int}>
     */
    private function aggregateDateRange(
        string $startDate,
        string $endDate,
        string $companyName,
        string $filePrefix,
    ): array {
        $dateCounts  = [];
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $filePath = $this->logPath . "/{$filePrefix}{$currentDate}.txt";

            if (file_exists($filePath)) {
                $jsonData = file_get_contents($filePath);
                $data     = json_decode($jsonData, true);

                if (is_array($data)) {
                    $this->processItems($data, $currentDate, $companyName, $dateCounts);
                }
            }

            $currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
        }

        return $dateCounts;
    }

    /**
     * Process JSON items and update date counts.
     */
    private function processItems(array $items, string $date, string $companyName, array &$dateCounts): void
    {
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $company = $item['company_id'][1] ?? null;
            if ($company !== $companyName) {
                continue;
            }

            // Determine stage status
            $stageLabel = $item['stage_id'][1] ?? null;
            $stageKey   = match ($stageLabel) {
                'Done'         => 'berhasil',
                'Verified'     => 'verified',
                'New'          => 'gagal',
                'Open Pending' => 'open_pending',
                default        => null,
            };

            // Determine visit status
            $visitStatus = $item['visit_status'] ?? null;
            $visitKey    = match ($visitStatus) {
                'Visited'   => 'visited',
                'Not Visit' => 'not_visit',
                default     => null,
            };

            // Initialize date entry if needed
            if (!isset($dateCounts[$date])) {
                $dateCounts[$date] = [
                    'berhasil'     => 0,
                    'gagal'        => 0,
                    'open_pending' => 0,
                    'verified'     => 0,
                    'visited'      => 0,
                    'not_visit'    => 0,
                ];
            }

            if ($stageKey !== null) {
                $dateCounts[$date][$stageKey]++;
            }

            if ($visitKey !== null) {
                $dateCounts[$date][$visitKey]++;
            }
        }
    }

    /**
     * Build the chart data array from date counts.
     *
     * @return list<array<string, mixed>>
     */
    private function buildChartData(array $dateCounts, string $monday, string $sunday, string $suffix = ''): array
    {
        $chartData   = [];
        $currentDate = $monday;

        while ($currentDate <= $sunday) {
            $entry = ['tanggal' => $currentDate];

            foreach (['berhasil', 'gagal', 'open_pending', 'verified', 'visited', 'not_visit'] as $key) {
                $entry[$key . $suffix] = $dateCounts[$currentDate][$key] ?? 0;
            }

            $chartData[] = $entry;
            $currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
        }

        return $chartData;
    }

    /**
     * Read the last_get_data or updated_data metadata from a log file.
     */
    public function getLastUpdate(string $filePrefix = 'RESULT_'): ?string
    {
        $date     = date('Y-m-d');
        $filePath = $this->logPath . "/{$filePrefix}{$date}.txt";

        if (!file_exists($filePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($filePath), true);

        return $data['last_get_data']['selesai']
            ?? $data['updated_data']['selesai']
            ?? null;
    }
}
