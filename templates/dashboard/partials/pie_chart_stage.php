<div class="container-fluid p-0 m-0 col-xl-12" id="pie_chart_current_month" style="display: none;">
    <div class="card shadow">
        <div class="card-header">
            <h3 class="text-center text-bold">Current Month Tickets - <?php echo htmlspecialchars($company_id); ?></h3>
        </div>
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="chart-pie-stages"></div>
            </figure>
            Last update: <?php echo htmlspecialchars($lastModified); ?>
        </div>
    </div>
</div>

<script>
(function() {
    var pieChartData       = <?php echo json_encode($PieChartSeries); ?>;
    var piechartSeriesData = Object.keys(pieChartData).map(function(stage) {
        var color;
        switch (stage) {
            case 'New':                       color = '#FFFF00'; break;
            case 'Solved':                    color = '#008000'; break;
            case 'Pending':                   color = '#FF0000'; break;
            case 'Cancel':                    color = '#A52A2A'; break;
            case 'Waiting For Verification':  color = '#0000FF'; break;
        }
        return { name: stage, y: pieChartData[stage], color: color };
    });

    Highcharts.chart('chart-pie-stages', {
        chart: { type: 'pie' },
        title: { text: '' },
        tooltip: { pointFormat: '<b>{point.name}:</b> {point.y} ({point.percentage:.1f}%)' },
        plotOptions: {
            series: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: [{
                    enabled: true,
                    distance: 10,
                    formatter: function() {
                        var label = this.y <= 1 ? 'Ticket' : 'Tickets';
                        return this.point.name + ': ' + this.y + ' ' + label;
                    },
                    style: { fontSize: '0.8em' }
                }, {
                    enabled: true,
                    distance: -60,
                    format: '{point.percentage:.1f}%',
                    style: { fontSize: '0.7em', textOutline: 'none', opacity: 1 }
                }],
                showInLegend: true
            }
        },
        series: [{ name: 'Percentage', colorByPoint: true, data: piechartSeriesData }]
    });
})();
</script>
