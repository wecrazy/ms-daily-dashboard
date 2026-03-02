<?php
/**
 * Partner Dashboard Template
 *
 * This single template replaces ARTAJASA.php, CIMB_NIAGA.php, DANA.php,
 * MANDIRI.php, MTI.php, NDP.php, OVO.php — all of which were identical
 * except for the redirect target.
 *
 * Variables available from the front controller:
 *   $company_id          - Display name (e.g., "ARTAJASA")
 *   $redirectUrl         - Next partner URL
 *   $redirectMs          - Redirect timeout in milliseconds
 *   $jsonChartData       - Last week chart JSON
 *   $jsonChartDataWeek   - Running week chart JSON
 *   $jsonChartData_now   - Real-time chart JSON
 *   $PieChartSeries      - Stage pie chart data
 *   $orderedSeriesPieChart - Stage → task type data
 *   $slaDaysGroup        - SLA days grouping
 *   $slaHoursGroup       - SLA hours grouping
 *   $slaPrevMonth        - SLA previous month
 *   $lastModified        - Stage data last modified
 *   $slaLastModified     - SLA data last modified
 *   $lastUpdateSchedule  - Scheduled data last update
 *   $lastUpdateNow       - Real-time data last update
 *   $companyName         - Same as $company_id
 *   $assetsBase          - Assets base URL
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../templates/layout/head.php'; ?>
</head>
<body class="hold-transition sidebar-collapse">
<div class="wrapper">
    <!-- Content Wrapper -->
    <div class="content-wrapper">

        <!-- Content Header -->
        <?php require __DIR__ . '/partials/content_header.php'; ?>

        <!-- Main content -->
        <div class="content">
            <div class="container">

                <!-- Loading spinner -->
                <?php require __DIR__ . '/partials/loading.php'; ?>

                <div class="container-fluid" id="main_data" style="display: block;">

                    <div class="row">
                        <!-- LAST WEEK -->
                        <?php require __DIR__ . '/partials/last_week_graphic.php'; ?>
                    </div>

                    <div class="row">
                        <!-- CURRENT WEEK -->
                        <?php require __DIR__ . '/partials/running_week_graphic.php'; ?>
                    </div>

                    <div class="row">
                        <!-- TODAY (REAL TIME) -->
                        <?php require __DIR__ . '/partials/running_week_graphic_realtime.php'; ?>
                    </div>

                    <div class="row">
                        <!-- PIE CHART: STAGE -->
                        <?php require __DIR__ . '/partials/pie_chart_stage.php'; ?>
                    </div>

                    <div class="row">
                        <!-- PIE CHART: STAGE × TICKET TYPE -->
                        <?php require __DIR__ . '/partials/pie_chart_stage_ticket_type.php'; ?>
                    </div>

                    <div class="row">
                        <!-- PIE CHART: SLA DEADLINE -->
                        <?php require __DIR__ . '/partials/pie_chart_sla_deadline.php'; ?>
                    </div>

                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- /.container -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark"></aside>

    <!-- Footer -->
    <?php require __DIR__ . '/../../templates/layout/footer.php'; ?>

</div>
<!-- ./wrapper -->

<!-- Scripts -->
<?php require __DIR__ . '/../../templates/layout/scripts.php'; ?>

<!-- Hide loading, show data -->
<script>
    document.getElementById("loading").style.display = "none";
    document.getElementById("main_data").style.display = "block";
</script>

<!-- Logout -->
<script>
    function logout() {
        document.cookie = "sessionreport=; path=/;";
        window.location.href = "<?php echo $basePath ?? ''; ?>/login";
    }
</script>

<!-- Slideshow & Auto-redirect -->
<script src="<?php echo $assetsBase; ?>/js/dashboard.js"></script>
<script>
    // Initialize slideshow
    initSlideshow();

    // Auto-redirect to next partner
    setTimeout(function() {
        window.location.href = "<?php echo $redirectUrl; ?>";
    }, <?php echo $redirectMs; ?>);
</script>

</body>
</html>
