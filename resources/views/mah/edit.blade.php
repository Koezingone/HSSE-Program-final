@extends('adminlte::page')

@section('title', 'Edit MAH Register')

{{-- Tambahkan ini untuk plugin Select2 (dropdown yang bisa dicari) --}}
@section('plugins.Select2', true)

@section('content_header')
<h1>Edit MAH Register: {{ $register->mah_id }}</h1>
@stop

@section('content')
<div class="card card-warning"> {{-- Ubah warna card jadi kuning untuk edit --}}
    <div class="card-header">
        <h3 class="card-title">Formulir Edit MAH Register</h3>
    </div>

    {{-- Ubah action ke route 'update' dan method ke 'PUT' --}}
    <form action="{{ route('mah-register.update', $register->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- PENTING: Method 'PUT' untuk update --}}

        <div class="card-body">
            {{-- Baris 1: MAH ID & Hazard Category --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mah_id">MAH ID</label>
                        {{-- Tambahkan 'readonly' --}}
                        <input type="text" class="form-control" name="mah_id" value="{{ $register->mah_id }}" readonly required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="hazard_category">Hazard Category</label>
                        <select class="form-control select2" name="hazard_category" style="width: 100%;">
                            <option value="">-- Pilih Kategori Hazard --</option>
                            @foreach($hazard_categories as $category)
                            {{-- Tambahkan 'selected' jika data cocok --}}
                            <option value="{{ $category }}" {{ $register->hazard_category == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Baris 2: Major Accident Hazard & Cause --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="major_accident_hazard">Major Accident Hazard</label>
                        <input type="text" class="form-control" name="major_accident_hazard" value="{{ $register->major_accident_hazard }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cause">Cause</label>
                        <select class="form-control select2" name="cause" style="width: 100%;">
                            <option value="">-- Pilih Penyebab --</option>
                            @foreach($causes as $cause)
                            {{-- Tambahkan 'selected' jika data cocok --}}
                            <option value="{{ $cause }}" {{ $register->cause == $cause ? 'selected' : '' }}>
                                {{ $cause }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Baris 3: Top Event & Consequences --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="top_event">Top Event</label>
                        <select class="form-control select2" name="top_event" style="width: 100%;">
                            <option value="">-- Pilih Top Event --</option>
                            @foreach($top_events as $event)
                            {{-- Tambahkan 'selected' jika data cocok --}}
                            <option value="{{ $event }}" {{ $register->top_event == $event ? 'selected' : '' }}>
                                {{ $event }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="consequences">Consequences (Textarea)</label>
                        {{-- Isi data di dalam textarea --}}
                        <textarea class="form-control" name="consequences" rows="3">{{ $register->consequences }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Baris 4: Risk --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="initial_risk">Initial Risk (Angka)</label>
                        <input type="number" class="form-control" name="initial_risk" value="{{ $register->initial_risk }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="residual_risk">Residual Risk (Angka)</label>
                        <input type="number" class="form-control" name="residual_risk" value="{{ $register->residual_risk }}">
                    </div>
                </div>
                <div class="col-md-4"> {{-- <-- BARU --}}
                    <div class="form-group">
                        <label for="final_risk">Final Risk (Angka)</label>
                        <input type="number" class="form-control" name="final_risk" value="{{ $register->final_risk }}" placeholder="Input RPN setelah mitigasi">
                    </div>
                </div>
            </div>

            {{-- Baris 5: Barriers --}}
            <div class="form-group">
                <label for="preventive_barriers">Preventive Barriers (Textarea)</label>
                <textarea class="form-control" name="preventive_barriers" rows="3">{{ $register->preventive_barriers }}</textarea>
            </div>
            <div class="form-group">
                <label for="mitigative_barriers">Mitigative Barriers (Textarea)</label>
                <textarea class="form-control" name="mitigative_barriers" rows="3">{{ $register->mitigative_barriers }}</textarea>
            </div>

            {{-- Baris 6: Rekomendasi & Referensi --}}
            <div class="form-group">
                <label for="rekomendasi">Rekomendasi (Textarea)</label>
                <textarea class="form-control" name="rekomendasi" rows="3">{{ $register->rekomendasi }}</textarea>
            </div>
            <div class="form-group">
                <label for="referensi_sudi">Referensi Studi</label>
                <input type="text" class="form-control" name="referensi_sudi" value="{{ $register->referensi_sudi }}">
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Update Data</button>
            <a href="{{ route('mah-register.index') }}" class="btn btn-default float-right">Batal</a>
        </div>
    </form>
</div>
@stop

{{-- Tambahkan ini di bagian akhir untuk mengaktifkan Select2 --}}
@section('js')
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2({
            theme: 'bootstrap4' // Menggunakan tema bootstrap 4
        });
    });
</script>
@stop