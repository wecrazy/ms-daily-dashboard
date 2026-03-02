<?php

declare(strict_types=1);

namespace MsDashboard\Service;

use MsDashboard\Config\Config;
use MsDashboard\Http\OdooClient;

/**
 * Exports Odoo data to flat-file storage (JSON in .txt files).
 *
 * Replaces: API/exportJSON.php, API/exportJSONNow.php, API/getStage.php, API/getSLADeadline.php
 */
final readonly class OdooExporter
{
    private OdooClient $client;
    private Config $config;
    private string $logPath;

    public function __construct(?OdooClient $client = null, ?Config $config = null)
    {
        $this->config  = $config ?? Config::load();
        $this->client  = $client ?? new OdooClient($this->config);
        $this->logPath = $this->config->logPath();
    }

    /**
     * Export scheduled task data (called by cron, replaces exportJSON.php).
     */
    public function exportTasks(): string
    {
        date_default_timezone_set($this->config->timezone());

        $companyIds = $this->config->getIntList('ODOO_COMPANY_IDS');

        $yesterday = date('Y-m-d', strtotime('-2 days'));
        $today     = date('Y-m-d', strtotime('yesterday'));

        $startTime = $yesterday . ' 16:00:00';
        $endTime   = $today . ' 17:00:00';

        $domain = [
            ['company_id', '=', $companyIds],
            ['stage_id', '=', ['New', 'Done', 'Open Pending', 'Verified']],
            ['planned_date_begin', '>=', $startTime],
            ['planned_date_begin', '<=', $endTime],
        ];

        $fields = [
            'helpdesk_ticket_id',
            'stage_id',
            'company_id',
            'planned_date_begin',
            'timesheet_timer_last_stop',
            'technician_id',
            'total_hours_spent',
        ];

        $result = $this->client->getData($domain, 'project.task', $fields);

        $data = $this->enrichWithVisitStatus($result);
        $data['last_get_data'] = ['selesai' => date('Y-m-d H:i:s')];

        $filePath = $this->logPath . '/RESULT_' . date('Y-m-d') . '.txt';
        file_put_contents($filePath, json_encode($data));

        $sizeKb = round(filesize($filePath) / 1024, 2);

        return "Data saved to {$filePath} ({$sizeKb} KB)";
    }

    /**
     * Export real-time task data (called by cron, replaces exportJSONNow.php).
     */
    public function exportTasksNow(): string
    {
        date_default_timezone_set($this->config->timezone());

        $companyIds = $this->config->getIntList('ODOO_COMPANY_IDS');

        $now     = date('Y-m-d H:i:s');
        $today00 = date('Y-m-d') . ' 00:00:00';

        $domain = [
            ['company_id', '=', $companyIds],
            ['stage_id', '=', ['New', 'Done', 'Open Pending', 'Verified', 'Requested']],
            ['planned_date_begin', '>=', $today00],
            ['planned_date_begin', '<=', $now],
        ];

        $fields = [
            'helpdesk_ticket_id',
            'stage_id',
            'company_id',
            'planned_date_begin',
            'timesheet_timer_last_stop',
            'technician_id',
            'total_hours_spent',
        ];

        $result = $this->client->getData($domain, 'project.task', $fields);

        $data = $this->enrichWithVisitStatus($result);
        $data['updated_data'] = ['selesai' => date('Y-m-d H:i:s')];

        $filePath = $this->logPath . '/RESULTNow_' . date('Y-m-d') . '.txt';
        file_put_contents($filePath, json_encode($data));

        $sizeKb = round(filesize($filePath) / 1024, 2);

        return "Data saved to {$filePath} ({$sizeKb} KB)";
    }

    /**
     * Export stage data (replaces API/getStage.php).
     */
    public function exportStages(): string
    {
        date_default_timezone_set($this->config->timezone());

        $ym01 = date('Y-m-01') . ' 00:00:00';
        $ymd  = date('Y-m-d') . ' 23:59:59';

        $domain = [
            ['create_date', '>=', $ym01],
            ['create_date', '<=', $ymd],
            ['company_id', '!=', ['HAUSJO', 'CIMB NIAGA']],
        ];

        $fields = ['company_id', 'stage_id', 'x_task_type'];

        $result = $this->client->getData($domain, 'helpdesk.ticket', $fields);

        $filePath = $this->logPath . '/RESULT_STAGE.txt';
        file_put_contents($filePath, json_encode($result));

        return "Stage data saved to {$filePath}";
    }

    /**
     * Export SLA deadline data (replaces API/getSLADeadline.php).
     */
    public function exportSlaDeadline(): string
    {
        date_default_timezone_set($this->config->timezone());

        $lastMonthYm01 = date('Y-m-01', strtotime('-1 month')) . ' 00:00:00';
        $ymt           = date('Y-m-t') . ' 23:59:59';

        $domain = [
            ['active', '=', true],
            ['create_date', '>=', $lastMonthYm01],
            ['create_date', '<=', $ymt],
            ['timesheet_timer_last_stop', '=', false],
            ['company_id', '!=', ['HAUSJO', 'CIMB NIAGA']],
        ];

        $fields = [
            'timesheet_timer_last_stop',
            'create_date',
            'company_id',
            'x_sla_deadline',
        ];

        $result = $this->client->getData($domain, 'project.task', $fields, 'id asc');

        $filePath = $this->logPath . '/RESULT_SLADEADLINE.txt';
        file_put_contents($filePath, json_encode($result));

        return "SLA deadline data saved to {$filePath}";
    }

    /**
     * Add visit_status field to each task.
     */
    private function enrichWithVisitStatus(array $tasks): array
    {
        foreach ($tasks as &$task) {
            if (!is_array($task)) {
                continue;
            }

            if (isset($task['timesheet_timer_last_stop'])) {
                $task['visit_status'] = ($task['timesheet_timer_last_stop'] === false)
                    ? 'Not Visit'
                    : 'Visited';
            } else {
                $task['visit_status'] = 'Not Available';
            }
        }

        return $tasks;
    }
}
