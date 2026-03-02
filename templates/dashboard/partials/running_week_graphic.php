<div class="container-fluid p-0 m-0 col-xl-12" id="summary_today" style="display: none;">
    <div class="card shadow">
        <div class="card-header">
            <h3 class="text-center text-bold">Technician Assignment - <?php echo htmlspecialchars($company_id); ?></h3>
        </div>
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="chart-running-week"></div>
            </figure>
            <i>Last Update: <?php echo htmlspecialchars($lastUpdateSchedule); ?></i>
        </div>
    </div>
</div>

<script>
(function() {
    var jsonData = <?php echo $jsonChartDataWeek; ?>;

    var weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    var categories = jsonData.map(function(item) {
        var date = new Date(item.tanggal);
        return weekdays[date.getDay()] + ", " + date.getDate() + " " + months[date.getMonth()] + " " + date.getFullYear();
    });

    var dataBerhasil     = jsonData.map(function(item) { return item.berhasil; });
    var dataGagal        = jsonData.map(function(item) { return item.gagal; });
    var dataOpen_Pending = jsonData.map(function(item) { return item.open_pending; });
    var dataVerified     = jsonData.map(function(item) { return item.verified; });
    var dataVisited      = jsonData.map(function(item) { return item.visited; });
    var dataNotVisit     = jsonData.map(function(item) { return item.not_visit; });

    // Check if all stacked data is zero → skip rendering
    var sumOfData = dataBerhasil.reduce(function(a, b) { return a + b; }, 0)
                  + dataOpen_Pending.reduce(function(a, b) { return a + b; }, 0)
                  + dataVerified.reduce(function(a, b) { return a + b; }, 0)
                  + dataGagal.reduce(function(a, b) { return a + b; }, 0);

    if (sumOfData === 0) return;

    Highcharts.chart('chart-running-week', {
        chart: { type: 'column' },
        title: { text: 'Weekly Task Report', align: 'left' },
        xAxis: { categories: categories },
        yAxis: [{ min: 0, title: { text: 'Number of Tasks' }, stackLabels: { enabled: true } }],
        legend: {
            align: 'center', verticalAlign: 'bottom', layout: 'horizontal',
            floating: false,
            backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || 'white',
            borderColor: '#CCC', borderWidth: 1, shadow: false
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '10px', fontWeight: 'light' },
                    formatter: function() { return this.y === 0 ? '' : this.y; }
                }
            },
            line: {
                marker: { enabled: true },
                dataLabels: {
                    enabled: true, y: 5, format: '{y}',
                    style: { fontSize: '14px', fontWeight: 'normal', color: '#333', textOutline: '1px contrast' }
                }
            }
        },
        series: [
            { name: 'Done', data: dataBerhasil, color: '#006400' },
            { name: 'Open Pending', data: dataOpen_Pending, color: '#FF0000' },
            { name: 'Verified', data: dataVerified, color: '#0000FF' },
            { name: 'Not Done', data: dataGagal, color: '#FFFF00' },
            { type: 'line', name: 'Visited', data: dataVisited, color: '#0aa14e' },
            { type: 'line', name: 'Not Visited', data: dataNotVisit, color: '#d204d9' }
        ]
    });
})();
</script>
