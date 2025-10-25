@extends('adminlte::page')

@section('title', 'Input Barrier Assessment Baru')

@section('plugins.Select2', true) {{-- Aktifkan Select2 --}}

@section('content_header')
<h1>Input Barrier Assessment Baru</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Formulir Input Penilaian Barrier</h3>
    </div>
    <form action="{{ route('barrier-assessments.store') }}" method="POST">
        @csrf
        <div class="card-body">

            {{-- Baris 1: Dropdown Bertingkat --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="barrier_category">Pilih Kategori Barrier</label>
                        <select class="form-control select2 @error('barrier_category') is-invalid @enderror" name="barrier_category" id="barrier_category" style="width: 100%;" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($barrierCategories as $category)
                            <option value="{{ $category }}" {{ old('barrier_category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('barrier_category') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="specific_barrier">Pilih Barrier Spesifik</label>
                        <select class="form-control select2 @error('specific_barrier') is-invalid @enderror" name="specific_barrier" id="specific_barrier" style="width: 100%;" required disabled>
                            <option value="">-- Pilih Kategori Dulu --</option>
                            {{-- Opsi diisi oleh JavaScript --}}
                        </select>
                        @error('specific_barrier') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Baris 2: Persentase & Tanggal --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="percentage">Persentase (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control @error('percentage') is-invalid @enderror" name="percentage" value="{{ old('percentage') }}" placeholder="Contoh: 95.50">
                        @error('percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="assessment_date">Tanggal Assessment (Opsional)</label>
                        <input type="date" class="form-control @error('assessment_date') is-invalid @enderror" name="assessment_date" value="{{ old('assessment_date') }}">
                        @error('assessment_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Baris 3: Catatan --}}
            <div class="form-group">
                <label for="notes">Catatan (Opsional)</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                @error('notes') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

        </div>
        <div class="card-footer"> <button type="submit" class="btn btn-primary">Simpan</button> </div>
    </form>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        const barrierOptions = @json($barrierOptions); // Data dari PHP
        const oldSpecificBarrier = "{{ old('specific_barrier') }}"; // Ambil nilai lama jika ada error validasi

        function populateSpecificBarriers(selectedCategory, selectedValue = null) {
            const specificBarrierSelect = $('#specific_barrier');
            specificBarrierSelect.empty().prop('disabled', true); // Kosongkan & disable

            if (selectedCategory && barrierOptions[selectedCategory]) {
                specificBarrierSelect.append('<option value="">-- Pilih Barrier Spesifik --</option>');
                // Loop melalui array object [{name:'...', type:'...'}, ...]
                barrierOptions[selectedCategory].forEach(function(barrier) {
                    const isSelected = (selectedValue === barrier.name); // Bandingkan dengan barrier.name
                    specificBarrierSelect.append(`<option value="${barrier.name}" ${isSelected ? 'selected' : ''}>${barrier.name}</option>`); // Value adalah barrier.name
                });
                specificBarrierSelect.prop('disabled', false); // Aktifkan
            } else {
                specificBarrierSelect.append('<option value="">-- Pilih Kategori Dulu --</option>');
            }
            specificBarrierSelect.select2({
                theme: 'bootstrap4'
            }); // Re-init Select2
        }

        // Panggil saat halaman dimuat (untuk handle old input)
        const initialCategory = $('#barrier_category').val();
        if (initialCategory) {
            populateSpecificBarriers(initialCategory, oldSpecificBarrier);
        }

        // Event listener saat Kategori Barrier berubah
        $('#barrier_category').on('change', function() {
            const selectedCategory = $(this).val();
            populateSpecificBarriers(selectedCategory); // Panggil fungsi populate
        });
    });
</script>
@stop