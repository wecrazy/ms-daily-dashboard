<?php

/**
 * Front Controller — Single entry point for all web requests.
 *
 * Routes:
 *   /                    → Redirect to first partner or login
 *   /login               → Login page
 *   /login/process       → Login API (POST)
 *   /dashboard/{partner} → Partner dashboard
 *   /technician/{partner}→ Technician summary (future)
 *   /logout              → Clear session, redirect to login
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MsDashboard\Auth\SessionAuth;
use MsDashboard\Config\Config;
use MsDashboard\Config\Partners;
use MsDashboard\Service\TaskDataService;
use MsDashboard\Service\StageDataService;
use MsDashboard\Service\SlaDataService;
use MsDashboard\Service\TechnicianDataService;

// Bootstrap
$config = Config::load();
date_default_timezone_set($config->timezone());

if ($config->debug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Parse route from REQUEST_URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Preserve token query param for mobile app (passed through all redirects)
$tokenParam = isset($_GET['token']) ? '?token=' . urlencode($_GET['token']) : '';

// Strip base path to get the route
$route = '/' . trim(substr($requestUri, strlen($basePath)), '/');
$route = $route === '//' ? '/' : $route;

// Routing
switch (true) {
    // Home — redirect to first partner or login
    case $route === '/' || $route === '':
        $auth = new SessionAuth();
        if ($auth->isAuthenticated()) {
            $rotation = Partners::rotation($config);
            header('Location: ' . $basePath . '/dashboard/' . $rotation[0] . $tokenParam);
        } else {
            header('Location: ' . $basePath . '/login');
        }
        exit;

    // Login page
    case $route === '/login' && $_SERVER['REQUEST_METHOD'] === 'GET':
        require __DIR__ . '/../templates/auth/login.php';
        exit;

    // Login API (POST)
    case $route === '/login/process' && $_SERVER['REQUEST_METHOD'] === 'POST':
        header('Content-Type: application/json');
        $auth     = new SessionAuth();
        $username = $_POST['userName'] ?? '';
        $password = $_POST['password'] ?? '';
        $result   = $auth->handleLoginRequest($username, $password);
        echo json_encode($result);
        exit;

    // Logout
    case $route === '/logout':
        setcookie('sessionreport', '', time() - 3600, '/');
        header('Location: ' . $basePath . '/login');
        exit;

    // Partner dashboard
    case preg_match('#^/dashboard/([A-Za-z_]+)$#', $route, $matches) === 1:
        $partnerSlug = strtoupper($matches[1]);

        if (!Partners::isValid($partnerSlug)) {
            http_response_code(404);
            echo 'Partner not found';
            exit;
        }

        $auth = new SessionAuth();
        $auth->requireAuth($basePath . '/login');

        // Prepare data for the template
        $company_id  = Partners::displayName($partnerSlug);
        $nextPartner = Partners::nextInRotation($partnerSlug, $config);
        $redirectUrl = $basePath . '/dashboard/' . $nextPartner . $tokenParam;
        $redirectMs  = $config->getInt('SLIDESHOW_REDIRECT_SECONDS', 120) * 1000;

        // Load services & data
        $taskService  = new TaskDataService($config);
        $stageService = new StageDataService($config);
        $slaService   = new SlaDataService($config);

        $lastWeekData      = $taskService->getLastWeekData($company_id);
        $runningWeekData   = $taskService->getRunningWeekData($company_id);
        $runningWeekNow    = $taskService->getRunningWeekNowData($company_id);
        $lastUpdateSchedule = $taskService->getLastUpdate('RESULT_');
        $lastUpdateNow     = $taskService->getLastUpdate('RESULTNow_');

        $stagePieData       = $stageService->getStagePieData($company_id);
        $stageTaskTypeData  = $stageService->getStageTaskTypePieData($company_id);
        $slaData            = $slaService->getSlaDeadlineData($company_id);

        // JSON-encode for JavaScript
        $jsonChartData     = json_encode($lastWeekData['chartData']);
        $jsonChartDataWeek = json_encode($runningWeekData['chartData']);
        $jsonChartData_now = json_encode($runningWeekNow['chartData']);

        // Template variables
        $PieChartSeries        = $stagePieData['series'];
        $lastModified          = $stagePieData['lastModified'];
        $orderedSeriesPieChart = $stageTaskTypeData['ordered'];
        $companyName           = $company_id;
        $slaDaysGroup          = $slaData['slaDaysGroup'];
        $slaHoursGroup         = $slaData['slaHoursGroup'];
        $slaPrevMonth          = $slaData['slaPrevMonth'];
        $slaLastModified       = $slaData['lastModified'];

        $assetsBase = $basePath . '/assets';

        require __DIR__ . '/../templates/dashboard/partner.php';
        exit;

    // Technician summary pages (future expansion)
    case preg_match('#^/technician/([A-Za-z_]+)$#', $route, $matches) === 1:
        $partnerSlug = strtoupper($matches[1]);

        if (!Partners::isValid($partnerSlug)) {
            http_response_code(404);
            echo 'Partner not found';
            exit;
        }

        $auth = new SessionAuth();
        $auth->requireAuth($basePath . '/login');

        $company_id = Partners::displayName($partnerSlug);
        $techService = new TechnicianDataService($config);

        $dailyData               = $techService->getDailyData();
        $top10MostVisited        = $techService->getTop10MostVisited();
        $top10LowestVisited      = $techService->getTop10LowestVisited();
        $top10MostTaskComplete   = $techService->getTop10MostTaskComplete();
        $top10LowestTaskComplete = $techService->getTop10LowestTaskComplete();

        $assetsBase = $basePath . '/assets';

        require __DIR__ . '/../templates/technician/summary.php';
        exit;

    // Static assets fallthrough (handled by Apache, this is for PHP built-in server)
    case str_starts_with($route, '/assets/'):
        return false;

    // 404
    default:
        http_response_code(404);
        echo '404 Not Found';
        exit;
}
