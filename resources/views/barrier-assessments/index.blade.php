@extends('adminlte::page')

@section('title', 'Daftar Barrier Assessment')

@section('plugins.Datatables', true) {{-- Aktifkan Datatables --}}

@section('content_header')
<h1>Daftar Barrier Assessment</h1>
@stop

@section('content')
@if (session('status'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('status') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tabel Data Assessment</h3>
        <a href="{{ route('barrier-assessments.create') }}" class="btn btn-primary btn-sm float-right"> <i class="fas fa-plus mr-1"></i> Tambah Baru </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="assessment_table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">No</th>
                        <th>Kategori Barrier</th>
                        <th>Barrier Spesifik</th>
                        <th>Tipe</th> {{-- Kolom Baru --}}
                        <th>Persentase (%)</th>
                        <th>Tgl. Assessment</th>
                        <th style="width: 120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $assessment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $assessment->barrier_category }}</td>
                        <td>{{ $assessment->specific_barrier }}</td>
                        {{-- Kolom Tipe Barrier --}}
                        <td>
                            @if($assessment->barrier_type == 'Hardware')
                            <span class="badge badge-info">Hardware</span>
                            @elseif($assessment->barrier_type == 'Human')
                            <span class="badge badge-warning">Human</span>
                            @else
                            {{ $assessment->barrier_type }}
                            @endif
                        </td>
                        <td>{{ !is_null($assessment->percentage) ? number_format($assessment->percentage, 2, ',', '.') : '-' }}</td>
                        <td>{{ $assessment->assessment_date ? \Carbon\Carbon::parse($assessment->assessment_date)->isoFormat('D MMM YYYY') : '-' }}</td>
                        <td>
                            <a href="{{ route('barrier-assessments.edit', $assessment->id) }}" class="btn btn-xs btn-warning" title="Edit"> <i class="fas fa-fw fa-edit"></i> </a>
                            <form action="{{ route('barrier-assessments.destroy', $assessment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus"> <i class="fas fa-fw fa-trash"></i> </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data assessment.</td> {{-- Colspan jadi 7 --}}
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function() {
        $("#assessment_table").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#assessment_table_wrapper .col-md-6:eq(0)');
    });
</script>
@stop