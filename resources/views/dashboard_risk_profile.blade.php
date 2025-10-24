@extends('adminlte::page')

@section('title', 'Dashboard Risk Profile')

{{-- Aktifkan plugin Chart.js --}}
@section('plugins.Chartjs', true)

@section('content_header')
<h1>Dashboard Risk Profile per Hazard Category</h1>
@stop

@section('content')
{{-- BARIS untuk BAR CHARTS per HAZARD CATEGORY (Residual vs Final) --}}
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Risk Profile (Residual vs Final) per Hazard Category</h3>
            </div>
            <div class="card-body">
                {{-- Kontainer untuk semua chart pair --}}
                <div class="row" id="risk-bar-charts-container">
                    {{-- Konten chart per kategori akan dimasukkan oleh JS --}}
                </div>
            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS --}}
@stop

@section('js')
<script>
    $(function() {
        // --- Data dari Controller ---
        var riskBarChartsData = {!!$riskBarChartsData!!};

        //--- Opsi untuk Bar Chart Individual ---
        var individualBarChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            },
            legend: {
                display: false
            }
        };

        //--- Looping Buat BANYAK BAR CHART ---
        var container = $('#risk-bar-charts-container');
        var index = 0;
        $.each(riskBarChartsData, function(hazardCategory, chartData) {
            var safeId = hazardCategory.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
            var residualCanvasId = `bar-residual-${safeId}-${index}`;
            var finalCanvasId = `bar-final-${safeId}-${index}`;
            var html = `<div class="col-md-6 mb-4"><div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">${hazardCategory}</h3></div><div class="card-body"><div class="row"><div class="col-6"><h5 class="text-center small">Residual Risk</h5><canvas id="${residualCanvasId}" style="min-height: 200px; height: 200px; max-height: 250px; max-width: 100%;"></canvas></div><div class="col-6"><h5 class="text-center small">Final Risk</h5><canvas id="${finalCanvasId}" style="min-height: 200px; height: 200px; max-height: 250px; max-width: 100%;"></canvas></div></div></div></div></div>`;
            container.append(html);

            // Buat Chart Residual
            if (chartData.residualData && chartData.residualData.some(v => v > 0)) {
                new Chart($(`#${residualCanvasId}`).get(0).getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Count',
                            data: chartData.residualData,
                            backgroundColor: chartData.colors
                        }]
                    },
                    options: individualBarChartOptions
                });
            } else {
                $(`#${residualCanvasId}`).parent().html('<h5 class="text-center small">Residual Risk</h5><p class="text-center text-muted mt-5">No data</p>');
            }

            // Buat Chart Final
            if (chartData.finalData && chartData.finalData.some(v => v > 0)) {
                new Chart($(`#${finalCanvasId}`).get(0).getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Count',
                            data: chartData.finalData,
                            backgroundColor: chartData.colors
                        }]
                    },
                    options: individualBarChartOptions
                });
            } else {
                $(`#${finalCanvasId}`).parent().html('<h5 class="text-center small">Final Risk</h5><p class="text-center text-muted mt-5">No data</p>');
            }
            index++;
        });
    });
</script>
@stop