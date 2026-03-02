/**
 * Dashboard slideshow — rotates through 6 chart sections on a timed loop.
 *
 * Section IDs (must exist in DOM):
 *   summary_last_week, summary_today, summary_today_real_time,
 *   pie_chart_current_month, piechart-stage-tickettype, pieChart_SLADeadline
 */

'use strict';

var SECTION_IDS = [
    'summary_last_week',
    'summary_today',
    'summary_today_real_time',
    'pie_chart_current_month',
    'piechart-stage-tickettype',
    'pieChart_SLADeadline'
];

function showSection(activeId) {
    SECTION_IDS.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = (id === activeId) ? 'block' : 'none';
        }
    });
}

function showLastWeek()    { showSection('summary_last_week'); }
function showSummaryToday(){ showSection('summary_today'); }
function realTime()        { showSection('summary_today_real_time'); }
function stagePie()        { showSection('pie_chart_current_month'); }
function ticketTypePie()   { showSection('piechart-stage-tickettype'); }
function slaPie()          { showSection('pieChart_SLADeadline'); }

function initSlideshow() {
    var steps = [
        { func: showLastWeek,     duration: 10000 },
        { func: showSummaryToday, duration: 12000 },
        { func: realTime,         duration:  7000 },
        { func: stagePie,         duration: 12000 },
        { func: ticketTypePie,    duration: 17000 },
        { func: slaPie,           duration: 20000 }
    ];

    var currentIndex = 0;

    function tick() {
        steps[currentIndex].func();
        currentIndex = (currentIndex + 1) % steps.length;
        setTimeout(tick, steps[currentIndex].duration);
    }

    tick();
}
