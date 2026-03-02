<div class="container-fluid p-0 m-0 col-xl-12" id="pieChart_SLADeadline" style="display: block;">
    <div class="card-header col-12 text-center p-0 m-0">
        <h4 class="text-bold"><?php echo htmlspecialchars($company_id); ?> - SLA Deadlines New Tasks</h4>
    </div>
    <div class="card-footer col-12 m-0 p-0 mb-2">
        &nbsp; &nbsp; <em>Last update: <?php echo htmlspecialchars($slaLastModified); ?></em>
    </div>
    <div class="row">
        <div class="col-4 p-1 m-0">
            <div class="card">
                <div class="card-header m-0 p-0 text-center text-bold">Tasks Previous Month</div>
                <div class="card-body">
                    <figure class="highcharts-figure">
                        <div id="chart-slaPrev"></div>
                    </figure>
                </div>
            </div>
        </div>
        <div class="col-4 p-1 m-0">
            <div class="card">
                <div class="card-header m-0 p-0 text-center text-bold">Tasks Current Month (Days)</div>
                <div class="card-body">
                    <figure class="highcharts-figure">
                        <div id="chart-slaDays"></div>
                    </figure>
                </div>
            </div>
        </div>
        <div class="col-4 p-1 m-0">
            <div class="card">
                <div class="card-header m-0 p-0 text-center text-bold">Tasks Current Day (Hours)</div>
                <div class="card-body">
                    <figure class="highcharts-figure">
                        <div id="chart-slaHours"></div>
                    </figure>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLA Days Current Month -->
<script>
(function() {
    var totalCount     = 0;
    var pieChartData   = <?php echo json_encode($slaDaysGroup); ?>;
    var dayColors      = { '3 days': '#FFFF00', '4 - 10 days': '#008000', '<3 days': '#FF0000', '>10 days': '#3b87d9' };
    var seriesData     = Object.keys(pieChartData).map(function(days) {
        totalCount += pieChartData[days];
        return { name: days, y: pieChartData[days], color: dayColors[days] || '#A52A2A' };
    });

    Highcharts.chart('chart-slaDays', {
        chart: { type: 'pie' },
        title: { text: '' },
        subtitle: {
            text: '* total ' + totalCount + ' tasks',
            style: { fontSize: '14px', fontFamily: 'Arial Narrow', fontStyle: 'italic' },
            align: 'left', verticalAlign: 'bottom'
        },
        tooltip: { pointFormat: '<b>{point.name}:</b> {point.y} ({point.percentage:.1f}%)' },
        plotOptions: {
            series: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: [{
                    enabled: true,
                    distance: 8,
                    formatter: function() {
                        if (this.y === 0) return null;
                        var label = this.y <= 1 ? 'Task' : 'Tasks';
                        var name  = this.point.name.replace(/days/gi, 'D');
                        return name + '(' + this.y + ' ' + label + ')';
                    },
                    style: { fontSize: '0.65em' }
                }, {
                    enabled: true,
                    distance: -30,
                    formatter: function() {
                        return this.percentage > 9 ? this.percentage.toFixed(2) + '%' : null;
                    },
                    style: { fontSize: '0.8em', textOutline: 'none', opacity: 1 }
                }],
                showInLegend: true
            }
        },
        series: [{ name: 'Percentage', colorByPoint: true, data: seriesData }]
    });
})();
</script>

<!-- SLA Hours Current Date -->
<script>
(function() {
    var totalCount     = 0;
    var pieChartData   = <?php echo json_encode($slaHoursGroup); ?>;
    var hourColors     = { '<1 hour': '#eb0231', '1 - 3 hours': '#ffff3b', '4 - 8 hours': '#31d408', '8 - 12 hours': '#3b87d9' };
    var seriesData     = Object.keys(pieChartData).map(function(hours) {
        totalCount += pieChartData[hours];
        return { name: hours, y: pieChartData[hours], color: hourColors[hours] || '#A52A2A' };
    });

    Highcharts.chart('chart-slaHours', {
        chart: { type: 'pie' },
        title: { text: '' },
        subtitle: {
            text: '* total ' + totalCount + ' tasks',
            style: { fontSize: '14px', fontFamily: 'Arial Narrow', fontStyle: 'italic' },
            align: 'left', verticalAlign: 'bottom'
        },
        tooltip: { pointFormat: '<b>{point.name}:</b> {point.y} ({point.percentage:.1f}%)' },
        plotOptions: {
            series: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: [{
                    enabled: true,
                    distance: 10,
                    formatter: function() {
                        if (this.y === 0) return null;
                        var label = this.y <= 1 ? 'Task' : 'Tasks';
                        var name  = this.point.name.replace(/hours/gi, 'h').replace(/hour/gi, 'h');
                        return name + '(' + this.y + ' ' + label + ')';
                    },
                    style: { fontSize: '0.65em' }
                }, {
                    enabled: true,
                    distance: -18,
                    formatter: function() {
                        return this.percentage > 10 ? this.percentage.toFixed(2) + '%' : null;
                    },
                    style: { fontSize: '0.6em', textOutline: 'none', opacity: 1 }
                }],
                showInLegend: true
            }
        },
        series: [{ name: 'Percentage', colorByPoint: true, data: seriesData }]
    });
})();
</script>

<!-- SLA Days Previous Month -->
<script>
(function() {
    var totalCount     = 0;
    var pieChartData   = <?php echo json_encode($slaPrevMonth); ?>;
    var dayColors      = { '3 days': '#FFFF00', '4 - 10 days': '#008000', '<3 days': '#FF0000', '>10 days': '#3b87d9' };
    var seriesData     = Object.keys(pieChartData).map(function(days) {
        totalCount += pieChartData[days];
        return { name: days, y: pieChartData[days], color: dayColors[days] || '#A52A2A' };
    });

    Highcharts.chart('chart-slaPrev', {
        chart: { type: 'pie' },
        title: { text: '' },
        subtitle: {
            text: '* total ' + totalCount + ' tasks',
            style: { fontSize: '14px', fontFamily: 'Arial Narrow', fontStyle: 'italic' },
            align: 'left', verticalAlign: 'bottom'
        },
        tooltip: { pointFormat: '<b>{point.name}:</b> {point.y} ({point.percentage:.1f}%)' },
        plotOptions: {
            series: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: [{
                    enabled: true,
                    distance: 8,
                    formatter: function() {
                        if (this.y === 0) return null;
                        var label = this.y <= 1 ? 'Task' : 'Tasks';
                        return this.point.name + '(' + this.y + ' ' + label + ')';
                    },
                    style: { fontSize: '0.65em' }
                }, {
                    enabled: true,
                    distance: -30,
                    formatter: function() {
                        return this.percentage > 9 ? this.percentage.toFixed(2) + '%' : null;
                    },
                    style: { fontSize: '0.8em', textOutline: 'none', opacity: 1 }
                }],
                showInLegend: true
            }
        },
        series: [{ name: 'Percentage', colorByPoint: true, data: seriesData }]
    });
})();
</script>
