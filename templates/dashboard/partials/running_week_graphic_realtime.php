<div class="container-fluid p-0 m-0 col-xl-12" id="summary_today_real_time" style="display: none;">
    <div class="card shadow">
        <div class="card-header">
            <h3 class="text-center text-bold">Today Technician Assignment - <?php echo htmlspecialchars($company_id); ?></h3>
        </div>
        <div class="card-body">
            <figure class="highcharts-figure">
                <div id="chart-realtime"></div>
            </figure>
            <i>Last Update Data: <?php echo htmlspecialchars($lastUpdateNow); ?></i>
        </div>
    </div>
</div>

<script>
(function() {
    var jsonData = <?php echo $jsonChartData_now; ?>;

    // Get today's date string
    var currentDate    = new Date();
    var currentDateStr = currentDate.toISOString().split('T')[0];

    var daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    var formattedDate = daysOfWeek[currentDate.getDay()] + ', '
                      + currentDate.getDate() + ' '
                      + monthNames[currentDate.getMonth()] + ' '
                      + currentDate.getFullYear();

    var categories = [formattedDate];

    var targetData = jsonData.find(function(item) {
        return item.tanggal === currentDateStr;
    });

    if (!targetData) return;

    var dataBerhasil     = [targetData.berhasil_now];
    var dataGagal        = [targetData.gagal_now];
    var dataOpen_Pending = [targetData.open_pending_now];
    var dataVerified     = [targetData.verified_now];
    var dataVisited      = [targetData.visited_now];
    var dataNotVisit     = [targetData.not_visit_now];

    var counts = [
        dataNotVisit.reduce(function(a, b) { return a + b; }, 0),
        dataVisited.reduce(function(a, b) { return a + b; }, 0)
    ];

    Highcharts.chart('chart-realtime', {
        chart: { type: 'column' },
        title: { text: 'Task Report', align: 'left' },
        subtitle: { text: 'Not Visited (' + counts[0] + '), Visited (' + counts[1] + ')' },
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
                    enabled: true, y: 0, format: '{y}',
                    style: { fontSize: '14px', fontWeight: 'normal', color: '#333', textOutline: '1px contrast' }
                }
            }
        },
        series: [
            { name: 'Done', data: dataBerhasil, color: '#006400' },
            { name: 'Open Pending', data: dataOpen_Pending, color: '#FF0000' },
            { name: 'Verified', data: dataVerified, color: '#0000FF' },
            { name: 'Not Done', data: dataGagal, color: '#FFFF00' }
        ]
    });
})();
</script>
