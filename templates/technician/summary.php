<?php
/**
 * Technician Summary Template
 *
 * Variables from front controller:
 *   $company_id               - Partner display name
 *   $top10MostVisited         - ['categories' => json, 'series_Visited' => json, 'series_Not_Visit' => json, 'series_total' => json, 'lastUpdate' => string]
 *   $top10LowestVisited       - Same structure
 *   $top10MostTaskComplete    - ['categories' => json, 'series_done' => json, 'series_verified' => json, 'series_open_pending' => json, 'series_total' => json, 'lastUpdate' => string]
 *   $top10LowestTaskComplete  - Same structure
 *   $assetsBase               - Assets base URL
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../templates/layout/head.php'; ?>
</head>
<body class="hold-transition sidebar-collapse">
<div class="wrapper">
    <div class="content-wrapper">

        <!-- Content Header -->
        <?php require __DIR__ . '/../dashboard/partials/content_header.php'; ?>

        <!-- Main content -->
        <div class="content">
            <div class="container">

                <!-- Loading spinner -->
                <?php require __DIR__ . '/../dashboard/partials/loading.php'; ?>

                <div class="container-fluid" id="main_data" style="display: block;">
                    <div class="row">

                        <!-- Top 10 Most Visited -->
                        <div class="container-fluid m-0 col-xl-6" id="top10most" style="display: block;">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h3 class="text-center text-bold"><?php echo htmlspecialchars($company_id); ?></h3>
                                </div>
                                <div class="card-body">
                                    <figure class="highcharts-figure">
                                        <div id="chart-most-visited"></div>
                                    </figure>
                                    <i>Last Update: <?php echo htmlspecialchars($top10MostVisited['lastUpdate'] ?? ''); ?></i>
                                </div>
                            </div>
                        </div>

                        <!-- Top 10 Lowest Visited -->
                        <div class="container-fluid m-0 col-xl-6" id="top10low" style="display: block;">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h3 class="text-center text-bold"><?php echo htmlspecialchars($company_id); ?></h3>
                                </div>
                                <div class="card-body">
                                    <figure class="highcharts-figure">
                                        <div id="chart-lowest-visited"></div>
                                    </figure>
                                    <i>Last Update: <?php echo htmlspecialchars($top10LowestVisited['lastUpdate'] ?? ''); ?></i>
                                </div>
                            </div>
                        </div>

                        <!-- Top 10 Most Task Complete -->
                        <div class="container-fluid m-0 col-xl-6" id="top10mostcomplete" style="display: block;">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h3 class="text-center text-bold"><?php echo htmlspecialchars($company_id); ?></h3>
                                </div>
                                <div class="card-body">
                                    <figure class="highcharts-figure">
                                        <div id="chart-most-complete"></div>
                                    </figure>
                                    <i>Last Update: <?php echo htmlspecialchars($top10MostTaskComplete['lastUpdate'] ?? ''); ?></i>
                                </div>
                            </div>
                        </div>

                        <!-- Top 10 Lowest Task Complete -->
                        <div class="container-fluid m-0 col-xl-6" id="top10lowcomplete" style="display: block;">
                            <div class="card shadow">
                                <div class="card-header">
                                    <h3 class="text-center text-bold"><?php echo htmlspecialchars($company_id); ?></h3>
                                </div>
                                <div class="card-body">
                                    <figure class="highcharts-figure">
                                        <div id="chart-lowest-complete"></div>
                                    </figure>
                                    <i>Last Update: <?php echo htmlspecialchars($top10LowestTaskComplete['lastUpdate'] ?? ''); ?></i>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <aside class="control-sidebar control-sidebar-dark"></aside>

    <?php require __DIR__ . '/../../templates/layout/footer.php'; ?>
</div>

<?php require __DIR__ . '/../../templates/layout/scripts.php'; ?>

<script>
    document.getElementById("loading").style.display = "none";
    document.getElementById("main_data").style.display = "block";
</script>

<script>
    function logout() {
        document.cookie = "sessionreport=; path=/;";
        window.location.href = "<?php echo $basePath ?? ''; ?>/login";
    }
</script>

<!-- Top 10 Most Visited Chart -->
<script>
(function() {
    Highcharts.chart('chart-most-visited', {
        chart: { type: 'bar' },
        title: { text: 'Top 10 Most Visited' },
        xAxis: { categories: <?php echo $top10MostVisited['categories']; ?> },
        yAxis: { min: 0, title: { text: 'Total Visited' }, allowDecimals: false },
        legend: { enabled: true },
        plotOptions: {
            series: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    style: { fontWeight: 'bold', fontSize: '11px' },
                    formatter: function() { return this.y === 0 ? '' : this.y; }
                }
            }
        },
        series: [
            { name: 'Visited', data: <?php echo $top10MostVisited['series_Visited']; ?>, color: '#00ff2f' },
            { name: 'Not Visited', data: <?php echo $top10MostVisited['series_Not_Visit']; ?>, color: '#e80e0e' },
            { name: 'Total', type: 'line', data: <?php echo $top10MostVisited['series_total']; ?>, color: '#0fbed1' }
        ]
    });
})();
</script>

<!-- Top 10 Lowest Visited Chart -->
<script>
(function() {
    Highcharts.chart('chart-lowest-visited', {
        chart: { type: 'bar' },
        title: { text: 'Top 10 Lowest Visited' },
        xAxis: { categories: <?php echo $top10LowestVisited['categories']; ?> },
        yAxis: { min: 0, title: { text: 'Total Visited' }, allowDecimals: false },
        legend: { enabled: true },
        plotOptions: {
            series: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    style: { fontWeight: 'bold', fontSize: '11px' },
                    formatter: function() { return this.y === 0 ? '' : this.y; }
                }
            }
        },
        series: [
            { name: 'Visited', data: <?php echo $top10LowestVisited['series_Visited']; ?>, color: '#00ff2f' },
            { name: 'Not Visited', data: <?php echo $top10LowestVisited['series_Not_Visit']; ?>, color: '#e80e0e' },
            { name: 'Total', type: 'line', data: <?php echo $top10LowestVisited['series_total']; ?>, color: '#0fbed1' }
        ]
    });
})();
</script>

<!-- Top 10 Most Task Complete Chart -->
<script>
(function() {
    Highcharts.chart('chart-most-complete', {
        chart: { type: 'bar' },
        title: { text: 'Top 10 Most Task Complete' },
        xAxis: { categories: <?php echo $top10MostTaskComplete['categories']; ?> },
        yAxis: { min: 0, title: { text: 'Total Visited' } },
        legend: { enabled: true },
        plotOptions: {
            series: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    style: { fontWeight: 'bold', fontSize: '11px' },
                    formatter: function() { return this.y === 0 ? '' : this.y; }
                }
            }
        },
        series: [
            { name: 'Done', data: <?php echo $top10MostTaskComplete['series_done']; ?>, color: '#006400' },
            { name: 'Verified', data: <?php echo $top10MostTaskComplete['series_verified']; ?>, color: '#0000FF' },
            { name: 'Open Pending', data: <?php echo $top10MostTaskComplete['series_open_pending']; ?>, color: '#FF0000' },
            { name: 'Total', type: 'line', data: <?php echo $top10MostTaskComplete['series_total']; ?>, color: '#000000' }
        ]
    });
})();
</script>

<!-- Top 10 Lowest Task Complete Chart -->
<script>
(function() {
    Highcharts.chart('chart-lowest-complete', {
        chart: { type: 'bar' },
        title: { text: 'Top 10 Lowest Task Complete' },
        xAxis: { categories: <?php echo $top10LowestTaskComplete['categories']; ?> },
        yAxis: { min: 0, title: { text: 'Total Visited' }, allowDecimals: false },
        legend: { enabled: true },
        plotOptions: {
            series: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    style: { fontWeight: 'bold', fontSize: '11px' },
                    formatter: function() { return this.y === 0 ? '' : this.y; }
                }
            }
        },
        series: [
            { name: 'Done', data: <?php echo $top10LowestTaskComplete['series_done']; ?>, color: '#006400' },
            { name: 'Verified', data: <?php echo $top10LowestTaskComplete['series_verified']; ?>, color: '#0000FF' },
            { name: 'Open Pending', data: <?php echo $top10LowestTaskComplete['series_open_pending']; ?>, color: '#FF0000' },
            { name: 'Total', type: 'line', data: <?php echo $top10LowestTaskComplete['series_total']; ?>, color: '#000000' }
        ]
    });
})();
</script>

</body>
</html>
