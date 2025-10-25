@extends('adminlte::page')

@section('title', 'Dashboard')

{{-- Aktifkan plugin Chart.js --}}
@section('plugins.Chartjs', true)

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #facilityMap {
        height: 500px;
    }

    .count-marker {
        background-color: rgba(255, 0, 0, 0.7);
        color: white;
        border-radius: 50%;
        text-align: center;
        font-weight: bold;
        line-height: 30px;
        width: 30px;
        height: 30px;
        border: 1px solid white;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        font-size: 14px;
    }

    .bowtie-layout {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .bowtie-category {
        border: 1px solid #ccc;
        padding: 10px;
        margin: 5px;
        flex: 1;
        min-height: 300px;
        /* Atur tinggi minimum */
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    .bowtie-top-event {
        border: 2px solid red;
        padding: 15px;
        text-align: center;
        font-weight: bold;
        background-color: #ffebeb;
        border-radius: 50%;
        width: 150px;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 15px;
        flex-shrink: 0;
    }

    .barrier-box {
        border: 1px solid #ddd;
        background-color: white;
        padding: 5px 8px;
        margin-bottom: 5px;
        font-size: 0.8rem;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .barrier-box .percentage {
        font-weight: bold;
        padding-left: 10px;
    }

    /* Warna berdasarkan persentase (contoh) */
    .perf-high {
        border-left: 5px solid #28a745;
    }

    /* Hijau >= 80 */
    .perf-medium {
        border-left: 5px solid #ffc107;
    }

    /* Kuning 50-79 */
    .perf-low {
        border-left: 5px solid #dc3545;
    }

    /* Merah < 50 */
    .perf-nodata {
        border-left: 5px solid #6c757d;
    }

    /* Abu-abu jika null */
    .barrier-type-icon {
        margin-left: 5px;
        font-size: 0.7rem;
    }

    /* Ikon tipe */
</style>
@stop

@section('content_header')
<h1>Dashboard MAH Register</h1>
@stop

@section('content')
{{-- BARIS 1 & 2: KOTAK INFO --}}
<div class="row">
    <div class="col-md-6">
        <div class="info-box"> <span class="info-box-icon bg-info elevation-1"><i class="fas fa-shield-alt"></i></span>
            <div class="info-box-content"> <span class="info-box-text">Total MAH</span> <span class="info-box-number">{{ $total_mah }}</span> </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box mb-3"> <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-tasks"></i></span>
            <div class="info-box-content"> <span class="info-box-text">Total Action Plan</span> <span class="info-box-number">{{ $total_actions }}</span> </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="info-box mb-3 bg-danger"> <span class="info-box-icon"><i class="fas fa-folder-open"></i></span>
            <div class="info-box-content"> <span class="info-box-text">MAH OPEN</span> <span class="info-box-number">{{ $overall_open }}</span> </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-3 bg-warning"> <span class="info-box-icon"><i class="fas fa-sync-alt"></i></span>
            <div class="info-box-content"> <span class="info-box-text">MAH ON PROGRESS</span> <span class="info-box-number">{{ $overall_on_progress }}</span> </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-3 bg-success"> <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content"> <span class="info-box-text">MAH CLOSE</span> <span class="info-box-number">{{ $overall_close }}</span> </div>
        </div>
    </div>
</div>
{{-- AKHIR KOTAK INFO --}}

{{-- (DIUBAH) BARIS 3: GRAFIK HAZARD CATEGORY & FINAL RISK --}}
<div class="row">
    {{-- Grafik Hazard Category (Pindah ke sini) --}}
    <div class="col-md-8"> {{-- Lebar diubah jadi 8 --}}
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Persentase MAH Hazard Category</h3>
            </div>
            <div class="card-body">
                <canvas id="mahCategoryPieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
    {{-- Grafik Final Risk Overall (Tetap di sini) --}}
    <div class="col-md-4">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Status FInal Risk MAH</h3>
            </div>
            <div class="card-body">
                <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS 3 --}}

{{-- (TETAP) BARIS 4: PIE CHARTS STATUS OVERALL per STUDI --}}
<div class="row">
    <div class="col-12">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Overall MAH Status per Tipe Studi</h3>
            </div>
            <div class="card-body">
                <div class="row" id="study-status-charts-container">
                    {{-- Konten 4 Pie Chart akan diisi JS --}}
                </div>
            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS 4 --}}

{{-- (BARU) BARIS 1: BOWTIE LAYOUT (HARDWARE) --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Ringkasan Performa Barrier - <i class="fas fa-cogs"></i> HARDWARE</h3>
            </div>
            <div class="card-body bowtie-layout">

                {{-- Kolom Kiri: Prevention (Hardware) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-success mb-3">Prevention Barriers</h5>
                    @forelse($groupedBarriersHardware['Prevention'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata';
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-cogs text-info barrier-type-icon" title="Hardware"></i>
                        </span>
                        <span class="percentage">
                            {{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}
                        </span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Hardware barriers assessed.</p>
                    @endforelse
                </div>

                {{-- Kolom Kiri Tengah: Detect & Control (Hardware) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-primary mb-3">Detect & Control</h5>
                    @forelse($groupedBarriersHardware['Detect and Control'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata';
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-cogs text-info barrier-type-icon" title="Hardware"></i>
                        </span>
                        <span class="percentage">{{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Hardware barriers assessed.</p>
                    @endforelse
                </div>

                {{-- Tengah: Top Event (Placeholder) --}}
                <div class="bowtie-top-event">
                    EVENT
                </div>

                {{-- Kolom Kanan Tengah: Mitigation (Hardware) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-warning mb-3">Mitigation Barriers</h5>
                    @forelse($groupedBarriersHardware['Mitigation'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata';
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-cogs text-info barrier-type-icon" title="Hardware"></i>
                        </span>
                        <span class="percentage">{{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Hardware barriers assessed.</p>
                    @endforelse
                </div>

                {{-- Kolom Kanan: Emergency Response (Hardware) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-danger mb-3">Emergency Response</h5>
                    @forelse($groupedBarriersHardware['Emergency Response'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata';
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-cogs text-info barrier-type-icon" title="Hardware"></i>
                        </span>
                        <span class="percentage">{{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Hardware barriers assessed.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS BOWTIE HARDWARE --}}


{{-- (BARU) BARIS 2: BOWTIE LAYOUT (HUMAN) --}}
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Ringkasan Performa Barrier - <i class="fas fa-user"></i> HUMAN</h3>
            </div>
            <div class="card-body bowtie-layout">

                {{-- Kolom Kiri: Prevention (Human) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-success mb-3">Prevention Barriers</h5>
                    @forelse($groupedBarriersHuman['Prevention'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata'; // Default
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-user text-warning barrier-type-icon" title="Human"></i>
                        </span>
                        <span class="percentage">
                            {{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}
                        </span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Human barriers assessed.</p>
                    @endforelse
                </div>

                {{-- Kolom Kiri Tengah: Detect & Control (Human) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-primary mb-3">Detect & Control</h5>
                    @forelse($groupedBarriersHuman['Detect and Control'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata';
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-user text-warning barrier-type-icon" title="Human"></i>
                        </span>
                        <span class="percentage">{{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Human barriers assessed.</p>
                    @endforelse
                </div>

                {{-- Tengah: Top Event (Placeholder) --}}
                <div class="bowtie-top-event">
                    EVENT
                </div>

                {{-- Kolom Kanan Tengah: Mitigation (Human) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-warning mb-3">Mitigation Barriers</h5>
                    @forelse($groupedBarriersHuman['Mitigation'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata';
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-user text-warning barrier-type-icon" title="Human"></i>
                        </span>
                        <span class="percentage">{{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Human barriers assessed.</p>
                    @endforelse
                </div>

                {{-- Kolom Kanan: Emergency Response (Human) --}}
                <div class="bowtie-category">
                    <h5 class="text-center text-danger mb-3">Emergency Response</h5>
                    @forelse($groupedBarriersHuman['Emergency Response'] ?? [] as $barrier)
                    @php
                    $perfClass = 'perf-nodata';
                    if (!is_null($barrier['percentage'])) {
                    if ($barrier['percentage'] >= 80) $perfClass = 'perf-high';
                    elseif ($barrier['percentage'] >= 50) $perfClass = 'perf-medium';
                    else $perfClass = 'perf-low';
                    }
                    @endphp
                    <div class="barrier-box {{ $perfClass }}">
                        <span>
                            {{ $barrier['name'] }}
                            <i class="fas fa-user text-warning barrier-type-icon" title="Human"></i>
                        </span>
                        <span class="percentage">{{ !is_null($barrier['percentage']) ? number_format($barrier['percentage'], 1).'%' : 'N/A' }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center small">No Human barriers assessed.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS BOWTIE HUMAN --}}

{{-- ... Chart lainnya ... --}}
{{-- AKHIR BARIS 6 --}}
{{-- AKHIR BARIS BOWTIE LAYOUT --}}

{{-- (BARU) KARTU UNTUK BARRIER PERFORMANCE CHART --}}
<div class="row">
        <div class="col-12">
                <div class="card card-default"> {{-- Ganti style card jika perlu --}}
                        <div class="card-header">
                                <h3 class="card-title">Barrier Performance (Hardware vs Human)</h3>
                            </div>
                        <div class="card-body">
                                <div class="chart">
                                        <canvas id="barrierPerformanceChart" style="min-height: 250px; height: 250px; max-height: 300px; max-width: 100%;"></canvas>
                                   
                </div>
                            </div>
                    </div>
            </div>
</div>
{{-- AKHIR KARTU BARRIER --}}

{{-- (DIUBAH) BARIS 6: GRAFIK STATUS ACTION PLAN (Pindah ke sini) --}}
<div class="row">
    <div class="col-md-12"> {{-- Lebar jadi penuh --}}
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Status Action Plan</h3>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 300px; max-width: 100%;"></canvas> {{-- Tinggi bisa disesuaikan --}}
                </div>
            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS 6 --}}


{{-- (BARU) BARIS UNTUK TABEL ACTION PLAN OPEN --}}
<div class="row">
    <div class="col-12">
        {{-- Gunakan card-warning agar cocok dengan status 'OPEN'/'ON PROGRESS' --}}
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">Daftar Progress Action Plan</h3>
            </div>
            <div class="card-body p-0"> {{-- p-0 agar tabel terlihat rapi --}}
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 15%;">MAH ID</th>
                                <th>Action Plan Description</th>
                                <th style="width: 20%;">Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Loop data dari controller --}}
                            @forelse($openActionPlans as $action)
                            <tr>
                                {{-- Tampilkan MAH ID dari relasi --}}
                                <td>{{ $action->mahRegister->mah_id ?? 'N/A' }}</td>
                                {{-- Tampilkan nama action plan --}}
                                <td>{{ $action->action_plan }}</td>
                                {{-- Tampilkan progress bar --}}
                                <td>
                                    @php $percentageValue = intval($action->persentase); @endphp
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-warning" role="progressbar"
                                            aria-valuenow="{{ $percentageValue }}" aria-valuemin="0"
                                            aria-valuemax="100" style="width: {{ $percentageValue }}%">
                                        </div>
                                    </div>
                                    <small class="d-block text-center">{{ $action->persentase }}</small>
                                </td>
                            </tr>
                            @empty
                            {{-- Jika tidak ada data OPEN --}}
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    <i class="fas fa-check-circle text-success fa-lg"></i>
                                    <p classam="mb-0 mt-1">Semua Action Plan sudah CLOSE.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS TABEL --}}


{{-- (DIUBAH) BARIS 5: PETA LOKASI (Pindah ke sini) --}}
<div class="row">
    <div class="col-12">
        <div class="card card-dark">
            <div class="card-header">
                <h3 class="card-title">Peta Lokasi MAH</h3>
            </div>
            <div class="card-body p-0">
                <div id="facilityMap"></div> {{-- Container peta --}}
            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS 5 --}}


{{-- (DIKEMBALIKAN) BARIS 5: BAR CHARTS per HAZARD CATEGORY (Residual dan Final) --}}
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Risk Profile (Residual vs Final) per Hazard Category</h3>
            </div>
            <div class="card-body">
                <div class="row" id="risk-bar-charts-container">
                    {{-- Konten chart per kategori akan dimasukkan oleh JS --}}
                </div>
            </div>
        </div>
    </div>
</div>
{{-- AKHIR BARIS 5 --}}

@stop

@section('js')
{{-- Skrip Leaflet --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    $(function() {
        // --- Data dari Controller (PHP ke JS) ---
        var actionStatusLabels = {!!$action_status_labels!!};
        var actionStatusValues = {!!$action_status_values!!};
        var finalRiskLabels = {!!$final_risk_labels!!}; // Pie Overall
        var finalRiskValues = {!!$final_risk_values!!}; // Pie Overall
        var mahCategoryLabels = {!!$hazard_category_labels!!};
        var mahCategoryValues = {!!$hazard_category_values!!};
        var studyStatusChartsData = {!!$studyStatusChartsData!!};
        var mapMarkersData = {!!$mapMarkersData!!};
        var riskBarChartsData = {!!$riskBarChartsData!!};
        // (BARU) Data Barrier Performance
        var barrierPerfLabels = {!!$barrierPerfLabels!!};
        var barrierPerfHardwareValues = {!!$barrierPerfHardwareValues!!};
        var barrierPerfHumanValues = {!!$barrierPerfHumanValues!!};

        //--- Opsi Standar ---
        var pieChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right'
            }
        };
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

        //--- Gambar Chart (Urutan tidak masalah, ID yang penting) ---
        // Chart Status Action Plan (Individual Bar Chart)
        new Chart($('#barChart').get(0).getContext('2d'), {
            type: 'bar',
            data: {
                labels: actionStatusLabels,
                datasets: [{
                    label: 'Jumlah Action Plan',
                    backgroundColor: 'rgba(40, 167, 69, 0.9)',
                    borderColor: 'rgba(40, 167, 69, 0.8)',
                    borderWidth: 1,
                    data: actionStatusValues
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        }); // Warna diubah ke hijau
        // Chart Final Risk (Overall Pie)
        new Chart($('#pieChart').get(0).getContext('2d'), {
            type: 'pie',
            data: {
                labels: finalRiskLabels,
                datasets: [{
                    data: finalRiskValues,
                    backgroundColor: ['#6c757d', '#00a65a', '#00c0ef', '#ffc107', '#f39c12', '#f56954', '#d2d6de'],
                }]
            },
            options: pieChartOptions
        }); // Urutan warna disesuaikan
        // Chart Hazard Category (Pie)
        new Chart($('#mahCategoryPieChart').get(0).getContext('2d'), {
            type: 'pie',
            data: {
                labels: mahCategoryLabels,
                datasets: [{
                    data: mahCategoryValues,
                    backgroundColor: ['#d2d6de', '#3c8dbc', '#00c0ef', '#f39c12', '#00a65a', '#f56954', '#8e44ad', '#2c3e50', '#7f8c8d'],
                }]
            },
            options: pieChartOptions
        });
        // Looping 4 PIE CHART Status per Studi
        var pieContainer = $('#study-status-charts-container');
        var pieIndex = 0;
        $.each(studyStatusChartsData, function(studyType, chartData) {
            /* ... kode looping pie studi sama ... */
            var safeStudyId = studyType.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
            var canvasId = `pie-study-${safeStudyId}-${pieIndex}`;
            var html = `<div class="col-md-3 mb-3"><div class="card card-outline card-secondary h-100"><div class="card-header text-center"><h3 class="card-title small">${studyType}</h3></div><div class="card-body d-flex align-items-center justify-content-center"><canvas id="${canvasId}" style="min-height: 150px; height: 150px; max-height: 200px; max-width: 100%;"></canvas></div></div></div>`;
            pieContainer.append(html);
            if (chartData) {
                new Chart($(`#${canvasId}`).get(0).getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.values,
                            backgroundColor: chartData.colors
                        }]
                    },
                    options: pieChartOptions
                });
            } else {
                $(`#${canvasId}`).parent().html('<p class="text-center text-muted my-auto">No data found.</p>');
                $(`#${canvasId}`).remove();
            }
            pieIndex++;
        });

        // --- Inisialisasi Peta Leaflet ---
        if (mapMarkersData && mapMarkersData.length > 0) {
            /* ... kode peta sama ... */
            var imageWidth = 1228;
            var imageHeight = 646;
            var bounds = [
                [0, 0],
                [imageHeight, imageWidth]
            ];
            var map = L.map('facilityMap', {
                crs: L.CRS.Simple,
                minZoom: -2,
                maxZoom: 2
            });
            var imageUrl = '{{ asset("images/map.png") }}';
            L.imageOverlay(imageUrl, bounds).addTo(map);
            map.fitBounds(bounds);
            map.setView([imageHeight / 2, imageWidth / 2], -1);
            mapMarkersData.forEach(function(markerInfo) {
                var countIcon = L.divIcon({
                    className: 'count-marker',
                    html: `<b>${markerInfo.count}</b>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                L.marker(markerInfo.coords, {
                    icon: countIcon
                }).addTo(map).bindTooltip(markerInfo.name + ": " + markerInfo.count + " MAH ID");
            });
        } else {
            $('#facilityMap').html('<p class="text-center text-muted my-5">Tidak ada data lokasi.</p>');
        }

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

        // --- (BARU) BAR CHART BARRIER PERFORMANCE PER CATEGORY ---
        var barrierPerfCanvas = $('#barrierPerformanceChart').get(0).getContext('2d');
        var barrierPerfData = {
            labels: barrierPerfLabels,
            datasets: [{
                    label: 'Hardware Barrier Avg %',
                    backgroundColor: 'rgba(60,141,188,0.9)', // Biru
                    borderColor: 'rgba(60,141,188,0.8)',
                    borderWidth: 1,
                    data: barrierPerfHardwareValues
                },
                {
                    label: 'Human Barrier Avg %',
                    backgroundColor: 'rgba(210, 214, 222, 1)', // Abu-abu
                    borderColor: 'rgba(210, 214, 222, 1)',
                    borderWidth: 1,
                    data: barrierPerfHumanValues
                }
            ]
        };
        var barrierPerfOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100, // Set maksimum sumbu Y ke 100%
                        // Tambahkan callback untuk format % jika perlu
                        // callback: function(value) { return value + "%" }
                    }
                }]
                // xAxes tidak perlu stacked
            },
            legend: {
                position: 'bottom' // Posisi legenda
            }
        };
        new Chart(barrierPerfCanvas, {
            type: 'bar',
            data: barrierPerfData,
            options: barrierPerfOptions
        });

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