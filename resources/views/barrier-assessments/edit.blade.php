@extends('adminlte::page')

@section('title', 'Edit Barrier Assessment')

{{-- Aktifkan plugin Select2 untuk dropdown yang bisa dicari --}}
@section('plugins.Select2', true)

@section('content_header')
<h1>Edit Barrier Assessment: {{ $assessment->specific_barrier }}</h1>
@stop

@section('content')
<div class="card card-warning"> {{-- Warna kuning untuk mode edit --}}
    <div class="card-header">
        <h3 class="card-title">Formulir Edit Barrier Assessment</h3>
    </div>

    <form action="{{ route('barrier-assessments.update', $assessment->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            {{-- Baris 1: Kategori Barrier & Specific Barrier (FIXED) --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="barrier_category">Barrier Category</label>
                        {{-- TAMBAHKAN ID 'barrier_category' --}}
                        <select name="barrier_category" id="barrier_category" class="form-control select2 @error('barrier_category') is-invalid @enderror" style="width: 100%;" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($barrierCategories as $category)
                            <option value="{{ $category }}"
                                {{-- Gunakan old() helper yang fallback ke data assessment --}}
                                {{ old('barrier_category', $assessment->barrier_category) == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                            @endforeach
                        </select>
                        @error('barrier_category') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    {{-- GANTI DROPDOWN INI DENGAN VERSI KOSONG (seperti create.blade.php) --}}
                    <div class="form-group">
                        <label for="specific_barrier">Specific Barrier</label>
                        <select class="form-control select2 @error('specific_barrier') is-invalid @enderror" name="specific_barrier" id="specific_barrier" style="width: 100%;" required>
                            {{-- Opsi diisi oleh JavaScript --}}
                            <option value="">-- Pilih Kategori Dulu --</option>
                        </select>
                        @error('specific_barrier') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Baris 2: Persentase & Tanggal (Layout disamakan dgn create.blade.php) --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="percentage">Percentage (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control @error('percentage') is-invalid @enderror" name="percentage"
                            value="{{ old('percentage', $assessment->percentage) }}" placeholder="Contoh: 95.50">
                        @error('percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="assessment_date">Assessment Date (Opsional)</label>
                        <input type="date" class="form-control @error('assessment_date') is-invalid @enderror" name="assessment_date"
                            value="{{ old('assessment_date', $assessment->assessment_date) }}">
                        @error('assessment_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Baris 3: Catatan --}}
            <div class="form-group">
                <label for="notes">Notes (Opsional)</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                    placeholder="Catatan tambahan...">{{ old('notes', $assessment->notes) }}</textarea>
                @error('notes') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            {{-- Input 'barrier_type' dihapus karena di-handle otomatis oleh Controller --}}
        </div>

        {{-- Tombol Submit --}}
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Update Data</button>
            <a href="{{ route('barrier-assessments.index') }}" class="btn btn-default float-right">Batal</a>
        </div>
    </form>
</div>
@stop

{{-- Script tambahan --}}
@section('js')
{{-- GANTI TOTAL SCRIPT JS DENGAN LOGIKA DARI create.blade.php --}}
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Ambil data lengkap dari controller
        const barrierOptions = @json($barrierOptions);

        // Ambil nilai yang harus dipilih:
        // 1. Cek 'old' input (jika validasi gagal)
        // 2. Jika tidak ada 'old' input, pakai data dari $assessment
        const selectedSpecificBarrier = "{{ old('specific_barrier', $assessment->specific_barrier) }}";

        /**
         * Fungsi untuk mengisi dropdown "Specific Barrier" berdasarkan kategori yang dipilih
         */
        function populateSpecificBarriers(selectedCategory, selectedValue = null) {
            const specificBarrierSelect = $('#specific_barrier');
            specificBarrierSelect.empty().prop('disabled', true); // Kosongkan & disable

            if (selectedCategory && barrierOptions[selectedCategory]) {
                specificBarrierSelect.append('<option value="">-- Pilih Barrier Spesifik --</option>');

                // Loop melalui data barrier untuk kategori yang dipilih
                barrierOptions[selectedCategory].forEach(function(barrier) {
                    const isSelected = (selectedValue === barrier.name);
                    specificBarrierSelect.append(`<option value="${barrier.name}" ${isSelected ? 'selected' : ''}>${barrier.name}</option>`);
                });

                specificBarrierSelect.prop('disabled', false); // Aktifkan kembali
            } else {
                specificBarrierSelect.append('<option value="">-- Pilih Kategori Dulu --</option>');
            }

            // Inisialisasi ulang Select2 agar ter-update
            specificBarrierSelect.select2({
                theme: 'bootstrap4'
            });
        }

        // --- LOGIKA UTAMA ---

        // 1. Ambil kategori yang terpilih saat halaman dimuat
        const initialCategory = $('#barrier_category').val();

        // 2. Jika ada kategori yang terpilih (seharusnya selalu ada di mode edit),
        //    langsung isi dropdown specific barrier dengan data yang sesuai
        if (initialCategory) {
            populateSpecificBarriers(initialCategory, selectedSpecificBarrier);
        }

        // 3. Tambahkan event listener untuk "on change" di dropdown kategori
        $('#barrier_category').on('change', function() {
            const selectedCategory = $(this).val();
            // Panggil fungsi populate, tapi reset 'selectedValue' (biarkan user memilih)
            populateSpecificBarriers(selectedCategory);
        });
    });
</script>
@stop