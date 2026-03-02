<?php

declare(strict_types=1);

namespace MsDashboard\Service;

use DateTime;
use DateTimeZone;
use MsDashboard\Config\Config;

/**
 * Reads RESULT_SLADEADLINE.txt and groups SLA deadline data.
 *
 * Replaces: controllers/json_sladeadline.php
 */
final readonly class SlaDataService
{
    private string $logPath;
    private string $timezone;

    public function __construct(?Config $config = null)
    {
        $config = $config ?? Config::load();
        $this->logPath  = $config->logPath();
        $this->timezone = $config->timezone();
    }

    /**
     * Get SLA deadline data grouped by days, hours, and previous month.
     *
     * @return array{
     *     slaDaysGroup: array<string, int>,
     *     slaHoursGroup: array<string, int>,
     *     slaPrevMonth: array<string, int>,
     *     lastModified: string
     * }
     */
    public function getSlaDeadlineData(string $companyName): array
    {
        $filePath = $this->logPath . '/RESULT_SLADEADLINE.txt';

        $empty = [
            'slaDaysGroup'  => [],
            'slaHoursGroup' => [],
            'slaPrevMonth'  => [],
            'lastModified'  => '',
        ];

        if (!file_exists($filePath)) {
            return $empty;
        }

        $data         = json_decode(file_get_contents($filePath), true);
        $lastModified = date('Y-m-d H:i:s', filemtime($filePath));

        if (!is_array($data)) {
            return array_merge($empty, ['lastModified' => $lastModified]);
        }

        $slaDaysGroup = [
            '<3 days'      => 0,
            '3 days'       => 0,
            '4 - 10 days'  => 0,
            '>10 days'     => 0,
        ];

        $slaHoursGroup = [
            '<1 hour'      => 0,
            '1 - 3 hours'  => 0,
            '4 - 8 hours'  => 0,
            '8 - 12 hours' => 0,
        ];

        $slaPrevMonth = [
            '<3 days'      => 0,
            '3 days'       => 0,
            '4 - 10 days'  => 0,
            '>10 days'     => 0,
        ];

        $tz            = new DateTimeZone($this->timezone);
        $currentDate   = new DateTime('now', $tz);
        $lastMonthDate = (clone $currentDate)->modify('-1 month');

        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $lastVisit   = $item['timesheet_timer_last_stop'] ?? '';
            $createDate  = $item['create_date'] ?? '';
            $company     = $item['company_id'][1] ?? '';
            $slaDeadline = $item['x_sla_deadline'] ?? '';

            if (empty($createDate) || empty($slaDeadline) || empty($company)) {
                continue;
            }

            if ($company !== $companyName || !empty($lastVisit)) {
                continue;
            }

            $createDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $createDate);
            $slaDeadlineDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $slaDeadline);

            if (!$createDateTime || !$slaDeadlineDateTime) {
                continue;
            }

            $slaDeadlineDateTime->modify('+7 hours');

            $createYm    = $createDateTime->format('Y-m');
            $currentYm   = $currentDate->format('Y-m');
            $lastMonthYm = $lastMonthDate->format('Y-m');

            if ($createYm === $currentYm) {
                $slaYm = $slaDeadlineDateTime->format('Y-m');
                if ($slaYm === $currentYm) {
                    $interval = $currentDate->diff($slaDeadlineDateTime);
                    $diffDays  = $interval->days;
                    $diffHours = $interval->h;

                    $this->classifyDays($diffDays, $slaDaysGroup);

                    if ($slaDeadlineDateTime->format('Y-m-d') === $currentDate->format('Y-m-d')) {
                        $this->classifyHours($diffHours, $slaHoursGroup);
                    }
                }
            } elseif ($createYm === $lastMonthYm) {
                $interval = $currentDate->diff($slaDeadlineDateTime);
                $this->classifyDays($interval->days, $slaPrevMonth);
            }
        }

        // Remove zero values
        $slaDaysGroup  = array_filter($slaDaysGroup);
        $slaHoursGroup = array_filter($slaHoursGroup);
        $slaPrevMonth  = array_filter($slaPrevMonth);

        return [
            'slaDaysGroup'  => $slaDaysGroup,
            'slaHoursGroup' => $slaHoursGroup,
            'slaPrevMonth'  => $slaPrevMonth,
            'lastModified'  => $lastModified,
        ];
    }

    private function classifyDays(int $days, array &$group): void
    {
        if ($days < 3) {
            $group['<3 days']++;
        } elseif ($days === 3) {
            $group['3 days']++;
        } elseif ($days >= 4 && $days <= 10) {
            $group['4 - 10 days']++;
        } else {
            $group['>10 days']++;
        }
    }

    private function classifyHours(int $hours, array &$group): void
    {
        if ($hours < 1) {
            $group['<1 hour']++;
        } elseif ($hours >= 1 && $hours <= 3) {
            $group['1 - 3 hours']++;
        } elseif ($hours >= 4 && $hours <= 8) {
            $group['4 - 8 hours']++;
        } elseif ($hours >= 8 && $hours <= 12) {
            $group['8 - 12 hours']++;
        }
    }
}
