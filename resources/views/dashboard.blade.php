@extends('adminlte::page')

@section('title', 'Dashboard')

{{-- Aktifkan plugin Chart.js --}}
@section('plugins.Chartjs', true)

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #facilityMap {
        height: 500px;
        /* Atau sesuaikan tinggi peta */
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
</style>
@stop

@section('content_header')
<h1>Dashboard MAH Register</h1>
@stop

@section('content')
{{-- BARIS 1 & 2: KOTAK INFO --}}
{{-- ... Kode Kotak Info Anda ... --}}
<div class="row">
    <div class="col-md-6">
        <div class="info-box"> <span class="info-box-icon bg-info elevation-1"><i class="fas fa-shield-alt"></i></span>
            <div class="info-box-content"> <span class="info-box-text">Total MAH</span> <span class="info-box-number">{{ $total_mah }}</span> </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-box mb-3"> <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-tasks"></i></span>
            <div class="info-box-content"> <span class="info-box-text">Total Action Plan (Individual)</span> <span class="info-box-number">{{ $total_actions }}</span> </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="info-box mb-3 bg-danger"> <span class="info-box-icon"><i class="fas fa-folder-open"></i></span>
            <div class="info-box-content"> <span class="info-box-text">STATUS OPEN</span> <span class="info-box-number">{{ $overall_open }}</span> </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-3 bg-warning"> <span class="info-box-icon"><i class="fas fa-sync-alt"></i></span>
            <div class="info-box-content"> <span class="info-box-text">STATUS ON PROGRESS</span> <span class="info-box-number">{{ $overall_on_progress }}</span> </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box mb-3 bg-success"> <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content"> <span class="info-box-text">STATUS CLOSE</span> <span class="info-box-number">{{ $overall_close }}</span> </div>
        </div>
    </div>
</div>

{{-- BARIS 3: GRAFIK LAIN --}}
{{-- ... Kode Bar Chart Status Individual & Pie Final Risk Overall ... --}}
<div class="row">
    <div class="col-md-8">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Status Action Plan (Individual)</h3>
            </div>
            <div class="card-body">
                <div class="chart"> <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas> </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Sebaran Final Risk (dari MAH Register)</h3>
            </div>
            <div class="card-body"> <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas> </div>
        </div>
    </div>
</div>

{{-- (BARU) BARIS 6: PETA LOKASI --}}
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
{{-- AKHIR BARIS 6 --}}

{{-- BARIS 4: GRAFIK HAZARD CATEGORY --}}
{{-- ... Kode Pie Chart Hazard Category ... --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Persentase MAH Hazard Category</h3>
            </div>
            <div class="card-body"> <canvas id="mahCategoryPieChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas> </div>
        </div>
    </div>
</div>


{{-- (BARU) BARIS 6: PIE CHARTS STATUS OVERALL per STUDI --}}
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
{{-- AKHIR BARIS 6 --}}
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
        // (BARU) Data untuk 4 Pie Chart Status Studi
        var studyStatusChartsData = {!!$studyStatusChartsData!!};

        // (BARU) Data marker peta
        var mapMarkersData = {!!$mapMarkersData!!};


        //--- Opsi Standar ---
        var pieChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
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

        //--- Gambar Chart yang Sudah Ada ---
        new Chart($('#barChart').get(0).getContext('2d'), {
            type: 'bar',
            data: {
                labels: actionStatusLabels,
                datasets: [{
                    label: 'Jumlah Action Plan',
                    backgroundColor: 'rgba(60,141,188,0.9)',
                    borderColor: 'rgba(60,141,188,0.8)',
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
        });
        new Chart($('#pieChart').get(0).getContext('2d'), {
            type: 'pie',
            data: {
                labels: finalRiskLabels,
                datasets: [{
                    data: finalRiskValues,
                    backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de', '#6c757d'],
                }]
            },
            options: pieChartOptions
        }); // Tambah warna abu2 utk On Progress jika ada
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


        //--- (BARU) Looping 4 PIE CHART Status per Studi ---
        var pieContainer = $('#study-status-charts-container');
        var pieIndex = 0;

        $.each(studyStatusChartsData, function(studyType, chartData) {
            // Buat ID unik
            var safeStudyId = studyType.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase();
            var canvasId = `pie-study-${safeStudyId}-${pieIndex}`;

            // Buat struktur HTML (col-md-3 agar muat 4 dalam 1 baris)
            var html = `
                <div class="col-md-3 mb-3">
                    <div class="card card-outline card-secondary h-100"> {{-- h-100 agar tinggi card sama --}}
                        <div class="card-header text-center">
                            <h3 class="card-title small">${studyType}</h3>
                         </div>
                        <div class="card-body d-flex align-items-center justify-content-center"> {{-- Pusatkan canvas/pesan --}}
                            <canvas id="${canvasId}" style="min-height: 150px; height: 150px; max-height: 200px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                 </div>`;
            pieContainer.append(html);

            // Gambar Pie Chart jika ada data
            if (chartData) {
                new Chart($(`#${canvasId}`).get(0).getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: chartData.labels, // OPEN, ON PROGRESS, CLOSE
                        datasets: [{
                            data: chartData.values,
                            backgroundColor: chartData.colors // Merah, Kuning, Hijau
                        }]
                    },
                    options: pieChartOptions // Gunakan opsi pie standar
                });
            } else {
                // Tampilkan pesan 'No data' jika chartData null
                $(`#${canvasId}`).parent().html('<p class="text-center text-muted my-auto">No data found for this study type.</p>');
                $(`#${canvasId}`).remove(); // Hapus canvas kosong
            }

            pieIndex++;
        });

        // --- (BARU) Inisialisasi Peta Leaflet ---
        if (mapMarkersData && mapMarkersData.length > 0) {
            var imageWidth = 1228; // Lebar gambar Anda
            var imageHeight = 646; // Tinggi gambar Anda
            var bounds = [
                [0, 0],
                [imageHeight, imageWidth]
            ];

            var map = L.map('facilityMap', {
                crs: L.CRS.Simple,
                minZoom: -2,
                maxZoom: 2
            });
            var imageUrl = '{{ asset("images/map.png") }}'; // Path ke gambar Anda
            L.imageOverlay(imageUrl, bounds).addTo(map);
            map.fitBounds(bounds);
            map.setView([imageHeight / 2, imageWidth / 2], -1); // Pusatkan peta

            mapMarkersData.forEach(function(markerInfo) {
                var countIcon = L.divIcon({
                    className: 'count-marker',
                    html: `<b>${markerInfo.count}</b>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                L.marker(markerInfo.coords, {
                        icon: countIcon
                    })
                    .addTo(map)
                    .bindTooltip(markerInfo.name + ": " + markerInfo.count + " MAH ID");
            });
        } else {
            $('#facilityMap').html('<p class="text-center text-muted my-5">Tidak ada data lokasi tersedia untuk peta.</p>');
        }

    });
</script>
@stop