@extends('adminlte::page')

@section('title', 'Input Risk Control Baru')

@section('plugins.Select2', true)

@section('content_header')
<h1>Input Risk Control (Action Plan) Baru</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Input Risk Control</h3>
    </div>
    <form action="{{ route('risk-control.store') }}" method="POST">
        @csrf
        <div class="card-body">

            {{-- Baris 1: MAH ID --}}
            <div class="form-group">
                <label for="mah_register_id">Pilih MAH ID Terkait</label>
                <select class="form-control select2" name="mah_register_id" style="width: 100%;" required>
                    <option value="">-- Pilih MAH Register --</option>
                    @foreach($mah_registers as $mah)
                    <option value="{{ $mah->id }}">{{ $mah->mah_id }} ({{ $mah->top_event }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Baris 2: Action Plan & Progress --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="action_plan">Action Plan (Textarea)</label>
                        <textarea class="form-control" name="action_plan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="action_progress">Action Progress (Textarea)</label>
                        <textarea class="form-control" name="action_progress" rows="3"></textarea>
                    </div>
                </div>
            </div>

            {{-- Baris 3: Checklist --}}
            <div class="d-flex justify-content-between align-items-center">
                <label class="mb-0">Progress Checklist</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkAllProgress">
                    <label class="form-check-label" for="checkAllProgress">Checklist Semua</label>
                </div>
            </div>
            <div class="row p-2" style="border: 1px solid #ced4da; border-radius: .25rem;">
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input progress-check-item" type="checkbox" name="eng" value="1">
                        <label class="form-check-label">Engineering (Eng)</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input progress-check-item" type="checkbox" name="proc" value="1">
                        <label class="form-check-label">Procurement (Proc)</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input progress-check-item" type="checkbox" name="cons" value="1">
                        <label class="form-check-label">Construction (Cons)</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input progress-check-item" type="checkbox" name="comm" value="1">
                        <label class="form-check-label">Commissioning (Comm)</label>
                    </div>
                </div>
            </div>

            {{-- Baris 4: Tanggal Plan & Lokasi --}}
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="plan_complete_date">Plan Complete Date</label>
                        <input type="text" class="form-control" name="plan_complete_date" placeholder="Contoh: 2027 atau Q1 2025">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <select class="form-control select2" name="location" style="width: 100%;">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($locations as $location)
                            <option value="{{ $location }}">{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Baris 5: Field Otomatis --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="persentase">Persentase (Otomatis)</label>
                        <input type="text" class="form-control" name="persentase" placeholder="Otomatis" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="action_status">Action Status (Otomatis)</label>
                        <input type="text" class="form-control" name="action_status" placeholder="Otomatis" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="actual_complete_date">Actual Complete Date (Otomatis)</label>
                        <input type="text" class="form-control" name="actual_complete_date" placeholder="Otomatis" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="referensi_sudi">Referensi Studi</label>
                        <input type="text" class="form-control" name="referensi_sudi" placeholder="Contoh: HAZOP 2023">
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Logika Checklist Semua
        $('#checkAllProgress').on('click', function() {
            var isChecked = $(this).is(':checked');
            $('.progress-check-item').prop('checked', isChecked);
        });
        $('.progress-check-item').on('click', function() {
            if ($('.progress-check-item:not(:checked)').length > 0) {
                $('#checkAllProgress').prop('checked', false);
            } else {
                $('#checkAllProgress').prop('checked', true);
            }
        });
    });
</script>
@stop