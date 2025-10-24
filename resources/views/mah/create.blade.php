@extends('adminlte::page')

@section('title', 'Input MAH Register Baru')

{{-- Aktifkan plugin bawaan AdminLTE --}}
@section('plugins.Select2', true)

@section('css')
{{-- Styling tambahan untuk membuat select2 seperti input biasa --}}
<style>
    /* pastikan semua select2 lebar penuh */
    .select2-container {
        width: 100% !important;
    }

    /* gaya border & padding agar mirip form-control */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        background-color: #fff !important;
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem !important;
    }

    /* teks dalam select */
    .select2-container--bootstrap4 .select2-selection__rendered {
        color: #495057 !important;
        line-height: 1.5rem !important;
    }

    /* panah dropdown */
    .select2-container--bootstrap4 .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
        top: 0 !important;
        right: 0.75rem !important;
    }

    /* efek fokus biru */
    .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25) !important;
    }
</style>
@stop

@section('content_header')
<h1 class="mb-3">Input MAH Register Baru</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i> Formulir Input MAH Register</h3>
    </div>

    <form action="{{ route('mah-register.store') }}" method="POST">
        @csrf
        <div class="card-body">
            {{-- Baris 1 --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mah_id">MAH ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="mah_id" id="mah_id" placeholder="Contoh: MAH-BJM-001" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hazard_category">Hazard Category</label>
                        <select class="form-control select2" name="hazard_category" id="hazard_category">
                            <option value="">-- Pilih Kategori Hazard --</option>
                            @foreach($hazard_categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Baris 2 --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="major_accident_hazard">Major Accident Hazard</label>
                        <input type="text" class="form-control" name="major_accident_hazard" id="major_accident_hazard" placeholder="Contoh: 1.9.2, 2.8.1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cause">Cause</label>
                        <select class="form-control select2" name="cause" id="cause">
                            <option value="">-- Pilih Penyebab --</option>
                            @foreach($causes as $cause)
                            <option value="{{ $cause }}">{{ $cause }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Baris 3 --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="top_event">Top Event</label>
                        <select class="form-control select2" name="top_event" id="top_event">
                            <option value="">-- Pilih Top Event --</option>
                            @foreach($top_events as $event)
                            <option value="{{ $event }}">{{ $event }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="consequences">Consequences</label>
                        <textarea class="form-control" name="consequences" id="consequences" rows="3" placeholder="Tuliskan dampak atau akibatnya"></textarea>
                    </div>
                </div>
            </div>

            {{-- Baris 4 --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="initial_risk">Initial Risk</label>
                        <input type="number" class="form-control" name="initial_risk" id="initial_risk" placeholder="Contoh: 12">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="residual_risk">Residual Risk</label>
                        <input type="number" class="form-control" name="residual_risk" id="residual_risk" placeholder="Contoh: 8">
                    </div>
                </div>
                <div class="col-md-4"> {{-- <-- BARU --}}
                    <div class="form-group">
                        <label for="final_risk">Final Risk (Angka)</label>
                        <input type="number" class="form-control" name="final_risk" placeholder="Input RPN setelah mitigasi">
                    </div>
                </div>
            </div>

            {{-- Barriers --}}
            <div class="form-group">
                <label for="preventive_barriers">Preventive Barriers</label>
                <textarea class="form-control" name="preventive_barriers" id="preventive_barriers" rows="3" placeholder="Contoh: Sistem Deteksi Gas, SOP Shutdown, dll."></textarea>
            </div>

            <div class="form-group">
                <label for="mitigative_barriers">Mitigative Barriers</label>
                <textarea class="form-control" name="mitigative_barriers" id="mitigative_barriers" rows="3" placeholder="Contoh: Fire Water System, PPE, Training Evakuasi, dll."></textarea>
            </div>

            <div class="form-group">
                <label for="rekomendasi">Rekomendasi</label>
                <textarea class="form-control" name="rekomendasi" id="rekomendasi" rows="3" placeholder="Masukkan rekomendasi tambahan..."></textarea>
            </div>

            <div class="form-group">
                <label for="referensi_sudi">Referensi Studi</label>
                <input type="text" class="form-control" name="referensi_sudi" id="referensi_sudi" placeholder="Contoh: HAZOP 2023">
            </div>
        </div>

        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan Data
            </button>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // pastikan select2 aktif dengan theme bootstrap4
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: '-- Pilih --',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@stop