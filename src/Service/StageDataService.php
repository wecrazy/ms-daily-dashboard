<?php

declare(strict_types=1);

namespace MsDashboard\Service;

use MsDashboard\Config\Config;

/**
 * Reads RESULT_STAGE.txt and produces stage pie chart data.
 *
 * Replaces: controllers/json_stage_pie.php, controllers/json_stage_task_type.php
 */
final readonly class StageDataService
{
    private string $logPath;

    public function __construct(?Config $config = null)
    {
        $config = $config ?? Config::load();
        $this->logPath = $config->logPath();
    }

    /**
     * Get stage counts for a company (for pie chart).
     *
     * @return array{series: array<string, int>, lastModified: string}
     */
    public function getStagePieData(string $companyName): array
    {
        $filePath = $this->logPath . '/RESULT_STAGE.txt';

        if (!file_exists($filePath)) {
            return ['series' => [], 'lastModified' => ''];
        }

        $data         = json_decode(file_get_contents($filePath), true);
        $lastModified = date('Y-m-d H:i:s', filemtime($filePath));
        $series       = [];

        if (!is_array($data)) {
            return ['series' => [], 'lastModified' => $lastModified];
        }

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $company = $item['company_id'][1] ?? null;
            $stage   = $item['stage_id'][1] ?? null;

            if ($company === $companyName && $stage !== null) {
                $series[$stage] = ($series[$stage] ?? 0) + 1;
            }
        }

        return ['series' => $series, 'lastModified' => $lastModified];
    }

    /**
     * Get stage → task type breakdown for a company.
     *
     * @return array{series: array<string, array<string, int>>, ordered: array<string, array<string, int>>, lastModified: string}
     */
    public function getStageTaskTypePieData(string $companyName): array
    {
        $filePath = $this->logPath . '/RESULT_STAGE.txt';

        if (!file_exists($filePath)) {
            return ['series' => [], 'ordered' => [], 'lastModified' => ''];
        }

        $data         = json_decode(file_get_contents($filePath), true);
        $lastModified = date('Y-m-d H:i:s', filemtime($filePath));
        $series       = [];

        if (!is_array($data)) {
            return ['series' => [], 'ordered' => [], 'lastModified' => $lastModified];
        }

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $company  = $item['company_id'][1] ?? null;
            $stage    = $item['stage_id'][1] ?? null;
            $taskType = $item['x_task_type'] ?? null;

            if ($company === $companyName && $stage !== null) {
                if (!isset($series[$stage])) {
                    $series[$stage] = [];
                }
                if ($taskType !== null) {
                    $series[$stage][$taskType] = ($series[$stage][$taskType] ?? 0) + 1;
                }
            }
        }

        // Order by predefined stage sequence
        $stageOrder = ['New', 'Solved', 'Pending', 'Waiting For Verification', 'Cancel'];
        $ordered    = [];

        foreach ($stageOrder as $stage) {
            if (isset($series[$stage])) {
                $ordered[$stage] = $series[$stage];
            }
        }

        return [
            'series'       => $series,
            'ordered'      => $ordered,
            'lastModified' => $lastModified,
        ];
    }
}
