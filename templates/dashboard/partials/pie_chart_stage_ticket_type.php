<div class="col-12 p-0 m-0" id="piechart-stage-tickettype" style="display: none;">
    <div class="card">
        <div class="card-header text-center">
            <h4>CURRENT MONTH <?php echo htmlspecialchars($companyName); ?> TICKETS</h4>
        </div>
        <div class="card-footer p-0 m-0">
            &nbsp; <em>Last update: <?php echo htmlspecialchars($lastModified); ?></em>
        </div>
    </div>
    <div class="row" id="chartsStageTicketType">
        <!-- Dynamically generated stage pie charts -->
    </div>
</div>

<script>
(function() {
    function containerPieChart(idStage) {
        var safeId = idStage.replace(/ /g, '_');
        var html = '<div class="col-3 m-0 p-1" id="' + safeId + 'Container" style="display: block;">'
            + '<div class="card shadow">'
            + '<div class="card-header"><p style="font-size: 12px; font-weight: bold; text-align: center; margin: 0; padding: 0;">' + idStage + '</p></div>'
            + '<div class="card-body"><figure class="highcharts-figure"><div id="' + safeId + 'PieChart"></div></figure></div>'
            + '</div></div>';
        document.getElementById('chartsStageTicketType').insertAdjacentHTML('beforeend', html);
    }

    function pieChartForStageTicketType(idChart, seriesData) {
        Highcharts.chart(idChart.replace(/ /g, '_') + 'PieChart', {
            chart: { type: 'pie' },
            title: { text: '' },
            tooltip: { pointFormat: '<b>{point.name}:</b> {point.y} ({point.percentage:.1f}%)' },
            legend: { itemStyle: { fontSize: '12px' } },
            plotOptions: {
                series: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: [{
                        enabled: true,
                        distance: 8,
                        formatter: function() {
                            if (this.percentage <= 11) {
                                return this.percentage.toFixed(2) + '%';
                            }
                            return null;
                        },
                        style: { fontSize: '10px' }
                    }, {
                        enabled: true,
                        distance: -25,
                        formatter: function() {
                            return this.percentage > 11 ? this.percentage.toFixed(2) + '%' : null;
                        },
                        style: { fontSize: '0.7em', textOutline: 'none', opacity: 1 }
                    }],
                    showInLegend: true
                }
            },
            series: [{ name: 'Percentage', colorByPoint: true, data: seriesData }]
        });
    }

    var ticketTypeColors = {
        'Corrective Maintenance': '#fe30fe',
        'Preventive Maintenance': '#8ec127',
        'Installation':          '#fdfd2f',
        'Withdrawal':            '#0c8910',
        'Replacement':           '#ee1c23',
        'Re-Initialization':     '#1a4ab9'
    };

    var jsonStageTicketType = <?php echo json_encode($orderedSeriesPieChart); ?>;

    Object.keys(jsonStageTicketType).forEach(function(stage) {
        var ticketType = jsonStageTicketType[stage];
        var seriesData = Object.keys(ticketType).map(function(type) {
            return { name: type, y: ticketType[type], color: ticketTypeColors[type] || '#999' };
        });
        containerPieChart(stage);
        pieChartForStageTicketType(stage, seriesData);
    });
})();
</script>
