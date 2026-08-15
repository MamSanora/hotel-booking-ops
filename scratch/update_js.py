import re

file = 'd:/xampp/htdocs/Project_Sarana/Hotel_Booking_Ops/resources/views/admin/dashboard.blade.php'
with open(file, 'r', encoding='utf-8') as f:
    content = f.read()

start_marker = "    // ── ApexChart instances ────────────────────────────────────"
end_marker = "    // ── Boot: load default 7-day view ──────────────────────────"

if start_marker in content and end_marker in content:
    start_idx = content.find(start_marker)
    end_idx = content.find(end_marker)

    new_js = """    // ── Global Filter State ─────────────────────────────────────
    window.chartFilters = { guest_type: null, nationality: null, room_type: null };
    
    window.clearCrossFilters = function() {
        window.chartFilters = { guest_type: null, nationality: null, room_type: null };
        document.getElementById('clear-filters-btn').classList.add('hidden');
        triggerFilterUpdate();
    };

    function triggerFilterUpdate() {
        if (window.chartFilters.guest_type || window.chartFilters.nationality || window.chartFilters.room_type) {
            document.getElementById('clear-filters-btn').classList.remove('hidden');
        } else {
            document.getElementById('clear-filters-btn').classList.add('hidden');
        }
        
        const activePreset = document.querySelector('.analytics-preset-btn.active');
        if (activePreset && activePreset.id === 'preset-custom') {
            window.applyCustomRange();
        } else if (activePreset && activePreset.dataset.period) {
            activePreset.click();
        } else {
            loadAnalytics(null, null);
        }
    }

    // ── ApexChart instances ────────────────────────────────────
    const defaultOpts = {
        chart: { toolbar: { show: false }, fontFamily: 'Inter, sans-serif', animations: { easing: 'easeinout', speed: 600 } },
        noData: { text: 'No data for this period', style: { color: '#9ca3af', fontSize: '13px' } },
        states: { hover: { filter: { type: 'darken', value: 0.9 } } }
    };

    const revenueChart = new ApexCharts(document.getElementById('chart-revenue'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'area', height: 240, group: 'timeline', id: 'revenue' },
        series: [{ name: 'Revenue ($)', data: [] }],
        xaxis: { categories: [], labels: { style: { fontSize: '11px' }, rotate: -35 }, tickAmount: 8 },
        yaxis: { labels: { formatter: v => '$' + v.toLocaleString() } },
        colors: [GOLD],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02 } },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: v => '$' + parseFloat(v).toFixed(2) } },
        grid: { borderColor: '#f3f4f6' },
    });
    revenueChart.render();

    const volumeChart = new ApexCharts(document.getElementById('chart-booking-volume'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'bar', height: 240, group: 'timeline', id: 'volume' },
        series: [{ name: 'Bookings', data: [] }],
        xaxis: { categories: [], labels: { style: { fontSize: '11px' }, rotate: -35 }, tickAmount: 8 },
        yaxis: { labels: { formatter: v => Math.round(v) } },
        colors: ['#6366f1'],
        plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f3f4f6' },
    });
    volumeChart.render();

    let rawRevenueByType = [];
    const typeChart = new ApexCharts(document.getElementById('chart-revenue-by-type'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'bar', height: 240, events: {
            dataPointSelection: function(e, chart, config) {
                const cat = config.w.config.xaxis.categories[config.dataPointIndex];
                if(cat) { window.chartFilters.room_type = cat; triggerFilterUpdate(); }
            }
        }},
        series: [{ name: 'Revenue ($)', data: [] }],
        xaxis: { categories: [] },
        yaxis: { labels: { formatter: v => '$' + v.toLocaleString() } },
        colors: ['#3b82f6'],
        plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '55%', distributed: true } },
        dataLabels: { enabled: true, formatter: v => '$' + parseFloat(v).toLocaleString(), style: { colors: ['#fff'] } },
        legend: { show: false },
        tooltip: {
            custom: function({series, seriesIndex, dataPointIndex, w}) {
                const item = rawRevenueByType[dataPointIndex];
                if(!item) return '';
                return '<div class="px-3 py-2 bg-white text-hotel-dark shadow rounded border border-gray-100">' +
                    '<b class="block mb-1 text-[0.8rem]">' + item.label + '</b>' +
                    '<div class="text-xs text-gray-500">Revenue: <span class="font-bold text-hotel-dark">$' + item.value.toLocaleString(undefined, {minimumFractionDigits:2}) + '</span></div>' +
                    '<div class="text-xs text-gray-500">Bookings: <span class="font-bold text-hotel-dark">' + item.booking_count + '</span></div>' +
                    '</div>';
            }
        },
        grid: { borderColor: '#f3f4f6' },
    });
    typeChart.render();

    const statusChart = new ApexCharts(document.getElementById('chart-booking-status'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'donut', height: 240 },
        series: [],
        labels: [],
        colors: COLORS,
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b,0) } } } } },
        dataLabels: { enabled: false },
        legend: { position: 'right', fontSize: '12px' },
    });
    statusChart.render();

    let rawRevenueByNat = [];
    const natChart = new ApexCharts(document.getElementById('chart-revenue-by-nationality'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'bar', height: 240, events: {
            dataPointSelection: function(e, chart, config) {
                const cat = config.w.config.xaxis.categories[config.dataPointIndex];
                if(cat) { window.chartFilters.nationality = cat; triggerFilterUpdate(); }
            }
        }},
        series: [{ name: 'Revenue ($)', data: [] }],
        xaxis: { categories: [] },
        yaxis: { labels: { formatter: v => '$' + v.toLocaleString() } },
        colors: ['#8b5cf6'],
        plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '55%', distributed: true } },
        dataLabels: { enabled: true, formatter: v => '$' + parseFloat(v).toLocaleString(), style: { colors: ['#fff'] } },
        legend: { show: false },
        tooltip: {
            custom: function({series, seriesIndex, dataPointIndex, w}) {
                const item = rawRevenueByNat[dataPointIndex];
                if(!item) return '';
                return '<div class="px-3 py-2 bg-white text-hotel-dark shadow rounded border border-gray-100">' +
                    '<b class="block mb-1 text-[0.8rem]">' + item.label + '</b>' +
                    '<div class="text-xs text-gray-500">Revenue: <span class="font-bold text-hotel-dark">$' + item.value.toLocaleString(undefined, {minimumFractionDigits:2}) + '</span></div>' +
                    '<div class="text-xs text-gray-500">Bookings: <span class="font-bold text-hotel-dark">' + item.booking_count + '</span></div>' +
                    '</div>';
            }
        },
        grid: { borderColor: '#f3f4f6' },
    });
    natChart.render();

    const guestTypeChart = new ApexCharts(document.getElementById('chart-volume-by-guest-type'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'donut', height: 240, events: {
            dataPointSelection: function(e, chart, config) {
                const cat = config.w.config.labels[config.dataPointIndex];
                if(cat) { window.chartFilters.guest_type = cat.toLowerCase(); triggerFilterUpdate(); }
            }
        }},
        series: [],
        labels: [],
        colors: ['#10b981', '#f59e0b', '#ef4444'],
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b,0) } } } } },
        dataLabels: { enabled: true, formatter: (val, opts) => opts.w.globals.seriesTotals[opts.seriesIndex] },
        legend: { position: 'right', fontSize: '12px' },
    });
    guestTypeChart.render();

    // ── Chart Resize Logic ─────────────────────────────────────
    const chartResizeObserver = new ResizeObserver(() => {
        window.dispatchEvent(new Event('resize'));
    });
    document.querySelectorAll('.chart-container').forEach(el => chartResizeObserver.observe(el));

    // ── KPI updater ────────────────────────────────────────────
    function updateKPIs(summary, label) {
        document.getElementById('kpi-revenue').textContent   = '$' + parseFloat(summary.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('kpi-bookings').textContent  = parseInt(summary.total_bookings).toLocaleString();
        document.getElementById('kpi-completed').textContent = parseInt(summary.completed_bookings).toLocaleString();
        document.getElementById('analytics-period-label').textContent = label;
    }

    // ── Main data fetch + chart update ─────────────────────────
    function loadAnalytics(startDate, endDate) {
        document.getElementById('kpi-revenue').textContent   = '…';
        document.getElementById('kpi-bookings').textContent  = '…';
        document.getElementById('kpi-completed').textContent = '…';

        let url = ANALYTICS_URL + '?';
        if (startDate && endDate) url += 'start_date=' + startDate + '&end_date=' + endDate + '&';
        
        if (window.chartFilters.guest_type) url += 'guest_type=' + encodeURIComponent(window.chartFilters.guest_type) + '&';
        if (window.chartFilters.nationality) url += 'nationality=' + encodeURIComponent(window.chartFilters.nationality) + '&';
        if (window.chartFilters.room_type) url += 'room_type=' + encodeURIComponent(window.chartFilters.room_type) + '&';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                revenueChart.updateOptions({
                    series: [{ name: 'Revenue ($)', data: data.revenue.map(d => d.value) }],
                    xaxis:  { categories: data.revenue.map(d => d.label) },
                });

                volumeChart.updateOptions({
                    series: [{ name: 'Bookings', data: data.bookingVolume.map(d => d.value) }],
                    xaxis:  { categories: data.bookingVolume.map(d => d.label) },
                });

                rawRevenueByType = data.revenueByType;
                typeChart.updateOptions({
                    series: [{ name: 'Revenue ($)', data: data.revenueByType.map(d => parseFloat(d.value)) }],
                    xaxis:  { categories: data.revenueByType.map(d => d.label) },
                    colors: data.revenueByType.map(d => d.color || '#3b82f6'),
                });

                if (data.bookingStatuses && data.bookingStatuses.length > 0) {
                    statusChart.updateOptions({
                        series: data.bookingStatuses.map(d => d.value),
                        labels: data.bookingStatuses.map(d => d.label),
                    });
                } else {
                    statusChart.updateOptions({ series: [], labels: [] });
                }

                rawRevenueByNat = data.revenueByNationality || [];
                natChart.updateOptions({
                    series: [{ name: 'Revenue ($)', data: rawRevenueByNat.map(d => parseFloat(d.value)) }],
                    xaxis:  { categories: rawRevenueByNat.map(d => d.label) },
                    colors: rawRevenueByNat.map((d, i) => COLORS[i % COLORS.length]),
                });

                if (data.volumeByGuestType && data.volumeByGuestType.length > 0) {
                    guestTypeChart.updateOptions({
                        series: data.volumeByGuestType.map(d => d.value),
                        labels: data.volumeByGuestType.map(d => d.label),
                    });
                } else {
                    guestTypeChart.updateOptions({ series: [], labels: [] });
                }

                updateKPIs(data.summary, data.period.label);
            })
            .catch((e) => {
                console.error(e);
                document.getElementById('analytics-period-label').textContent = 'Error loading data';
            });
    }

    // ── Preset buttons ─────────────────────────────────────────
    function setActivePreset(id) {
        document.querySelectorAll('.analytics-preset-btn').forEach(b => b.classList.remove('active'));
        const el = document.getElementById(id);
        if (el) el.classList.add('active');
    }

    document.querySelectorAll('.analytics-preset-btn[data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            setActivePreset(this.id);
            document.getElementById('analytics-custom-range').classList.add('hidden');

            if (period === 'all') {
                loadAnalytics(null, null);
                return;
            }
            const end   = new Date();
            const start = new Date();
            start.setDate(start.getDate() - (parseInt(period) - 1));
            loadAnalytics(fmtDate(start), fmtDate(end));
        });
    });

    function fmtDate(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }

    window.applyCustomRange = function() {
        const s = document.getElementById('custom-start').value;
        const e = document.getElementById('custom-end').value;
        if (!s || !e) { alert('Please select both a start and end date.'); return; }
        setActivePreset('preset-custom');
        loadAnalytics(s, e);
    };

    // Seed custom date inputs with sensible defaults
    const today = new Date();
    const monthAgo = new Date(); monthAgo.setDate(today.getDate() - 29);
    document.getElementById('custom-end').value   = fmtDate(today);
    document.getElementById('custom-start').value = fmtDate(monthAgo);

"""
    
    final_content = content[:start_idx] + new_js + content[end_idx:]
    with open(file, 'w', encoding='utf-8') as f:
        f.write(final_content)
    print('Replaced JS successfully.')
else:
    print('Markers not found')
