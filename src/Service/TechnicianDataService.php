<?php

declare(strict_types=1);

namespace MsDashboard\Service;

use MsDashboard\Config\Config;

/**
 * Reads techRESULT_{date}.txt files and produces technician summary data.
 *
 * Replaces: controllers/fetch_techdatadaily.php, fetch_techdataweekly.php,
 *           fetch_techdatamonthly.php, fetch_techdatatop10most.php,
 *           fetch_techdatatop10small.php, fetch_techdatatop10mosttaskcomplete.php,
 *           fetch_techdatatop10lowtaskcomplete.php
 */
final readonly class TechnicianDataService
{
    private string $logPath;

    public function __construct(?Config $config = null)
    {
        $config = $config ?? Config::load();
        $this->logPath = $config->logPath();
    }

    // =========================================================================
    // Daily / Weekly / Monthly aggregations
    // =========================================================================

    /**
     * Get daily technician stage data (single day).
     */
    public function getDailyData(): array
    {
        $data = $this->readTechFile(date('Y-m-d'));

        return $this->aggregateByStage($data);
    }

    /**
     * Get weekly technician stage data (last 7 days).
     */
    public function getWeeklyData(): array
    {
        $allData = $this->readMultipleDays(7);

        return $this->aggregateByStage($allData);
    }

    /**
     * Get monthly technician stage data (last 30 days).
     */
    public function getMonthlyData(): array
    {
        $allData = $this->readMultipleDays(30);

        return $this->aggregateByStage($allData);
    }

    // =========================================================================
    // Top 10 visit-based rankings
    // =========================================================================

    /**
     * Top 10 most visited technicians (descending).
     */
    public function getTop10MostVisited(): array
    {
        $data = $this->readTechFile(date('Y-m-d'));

        return $this->aggregateAndRankByVisit($data, 'desc', 10);
    }

    /**
     * Top 10 lowest visited technicians (ascending).
     */
    public function getTop10LowestVisited(): array
    {
        $data = $this->readTechFile(date('Y-m-d'));

        return $this->aggregateAndRankByVisit($data, 'asc', 10);
    }

    // =========================================================================
    // Top 10 task-complete rankings (only visited tasks)
    // =========================================================================

    /**
     * Top 10 most task-complete technicians (descending).
     */
    public function getTop10MostTaskComplete(): array
    {
        $data = $this->readTechFile(date('Y-m-d'));

        return $this->aggregateAndRankByTaskComplete($data, 'desc', 10);
    }

    /**
     * Top 10 lowest task-complete technicians (ascending).
     */
    public function getTop10LowestTaskComplete(): array
    {
        $data = $this->readTechFile(date('Y-m-d'));

        return $this->aggregateAndRankByTaskComplete($data, 'asc', 10);
    }

    // =========================================================================
    // Internal helpers
    // =========================================================================

    private function readTechFile(string $date): array
    {
        $filePath = $this->logPath . "/techRESULT_{$date}.txt";

        if (!file_exists($filePath)) {
            return [];
        }

        $data = json_decode(file_get_contents($filePath), true);

        return is_array($data) ? $data : [];
    }

    private function readMultipleDays(int $days): array
    {
        $allData = [];

        for ($i = 0; $i < $days; $i++) {
            $date     = date('Y-m-d', strtotime("-{$i} days"));
            $dayData  = $this->readTechFile($date);

            if (!empty($dayData)) {
                $allData = array_merge($allData, $dayData);
            }
        }

        return $allData;
    }

    /**
     * Aggregate technician data by stage, sort ascending by total.
     *
     * @return array{categories: string, series_done: string, series_verified: string, series_new: string, series_open_pending: string, series_total: string}
     */
    private function aggregateByStage(array $data): array
    {
        $users = [];

        foreach ($data as $item) {
            if (!is_array($item) || !isset($item['technician_id'][1], $item['stage_id'][1])) {
                continue;
            }

            $techId  = $item['technician_id'][1];
            $stageId = $item['stage_id'][1];

            if (!isset($users[$techId])) {
                $users[$techId] = [
                    'technician_id' => $techId,
                    'stages'        => ['Done' => 0, 'Verified' => 0, 'New' => 0, 'Open Pending' => 0],
                    'total_count'   => 0,
                ];
            }

            if (in_array($stageId, ['Done', 'Verified', 'New', 'Open Pending'], true)) {
                $users[$techId]['stages'][$stageId]++;
            }
            $users[$techId]['total_count']++;
        }

        usort($users, fn($a, $b) => $a['total_count'] - $b['total_count']);

        return $this->formatStageOutput($users);
    }

    /**
     * Aggregate by visit status and rank.
     */
    private function aggregateAndRankByVisit(array $data, string $direction, int $limit): array
    {
        $users = [];

        foreach ($data as $item) {
            if (!is_array($item) || !isset($item['technician_id'][1], $item['visit_status'])) {
                continue;
            }

            $techId      = $item['technician_id'][1];
            $visitStatus = $item['visit_status'];

            if (!isset($users[$techId])) {
                $users[$techId] = [
                    'technician_id' => $techId,
                    'visit_status'  => ['Visited' => 0, 'Not Visit' => 0],
                    'total_count'   => 0,
                ];
            }

            if (in_array($visitStatus, ['Visited', 'Not Visit'], true)) {
                $users[$techId]['visit_status'][$visitStatus]++;
            }
            $users[$techId]['total_count']++;
        }

        usort($users, fn($a, $b) => $direction === 'desc'
            ? $b['total_count'] - $a['total_count']
            : $a['total_count'] - $b['total_count']
        );

        $topUsers = array_slice($users, 0, $limit);

        return $this->formatVisitOutput($topUsers);
    }

    /**
     * Aggregate by task complete (visited only) and rank.
     */
    private function aggregateAndRankByTaskComplete(array $data, string $direction, int $limit): array
    {
        $users = [];

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            // Only count visited tasks
            if (($item['visit_status'] ?? '') !== 'Visited') {
                continue;
            }

            if (!isset($item['technician_id'][1], $item['stage_id'][1])) {
                continue;
            }

            $techId  = $item['technician_id'][1];
            $stageId = $item['stage_id'][1];

            if (!isset($users[$techId])) {
                $users[$techId] = [
                    'technician_id' => $techId,
                    'stages'        => ['Done' => 0, 'Verified' => 0, 'Open Pending' => 0],
                    'total_count'   => 0,
                ];
            }

            if (in_array($stageId, ['Done', 'Verified', 'Open Pending'], true)) {
                $users[$techId]['stages'][$stageId]++;
            }
            $users[$techId]['total_count']++;
        }

        usort($users, fn($a, $b) => $direction === 'desc'
            ? $b['total_count'] - $a['total_count']
            : $a['total_count'] - $b['total_count']
        );

        $topUsers = array_slice($users, 0, $limit);

        return $this->formatTaskCompleteOutput($topUsers);
    }

    /**
     * Format stage-based output for Highcharts.
     */
    private function formatStageOutput(array $users): array
    {
        $categories = $done = $verified = $new = $openPending = $total = [];

        foreach ($users as $user) {
            $categories[]   = $user['technician_id'];
            $done[]         = $user['stages']['Done'];
            $verified[]     = $user['stages']['Verified'];
            $new[]          = $user['stages']['New'];
            $openPending[]  = $user['stages']['Open Pending'];
            $total[]        = $user['total_count'];
        }

        return [
            'categories'        => json_encode($categories),
            'series_done'       => json_encode($done),
            'series_verified'   => json_encode($verified),
            'series_new'        => json_encode($new),
            'series_open_pending' => json_encode($openPending),
            'series_total'      => json_encode($total),
        ];
    }

    /**
     * Format visit-based output for Highcharts.
     */
    private function formatVisitOutput(array $users): array
    {
        $categories = $visited = $notVisit = $total = [];

        foreach ($users as $user) {
            $categories[] = $user['technician_id'];
            $visited[]    = $user['visit_status']['Visited'];
            $notVisit[]   = $user['visit_status']['Not Visit'];
            $total[]      = $user['total_count'];
        }

        return [
            'categories'      => json_encode($categories),
            'series_Visited'  => json_encode($visited),
            'series_Not_Visit' => json_encode($notVisit),
            'series_total'    => json_encode($total),
        ];
    }

    /**
     * Format task-complete output for Highcharts.
     */
    private function formatTaskCompleteOutput(array $users): array
    {
        $categories = $done = $verified = $openPending = $total = [];

        foreach ($users as $user) {
            $categories[]  = $user['technician_id'];
            $done[]        = $user['stages']['Done'];
            $verified[]    = $user['stages']['Verified'];
            $openPending[] = $user['stages']['Open Pending'];
            $total[]       = $user['total_count'];
        }

        return [
            'categories'          => json_encode($categories),
            'series_done'         => json_encode($done),
            'series_verified'     => json_encode($verified),
            'series_open_pending' => json_encode($openPending),
            'series_total'        => json_encode($total),
        ];
    }
}
