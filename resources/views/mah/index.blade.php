@extends('adminlte::page')

@section('title', 'Daftar MAH Register')

@section('content_header')
<h1>Daftar MAH Register</h1>
@stop

@section('content')
{{-- Ini untuk menampilkan pesan sukses setelah simpan, update, atau delete data --}}
@if (session('status'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
    {{ session('status') }}
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tabel Data MAH Register</h3>
    </div>
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">No</th>
                    <th>MAH ID</th>
                    <th>Hazard Category</th>
                    <th>Top Event</th>
                    <th>Overall Status</th> {{-- <-- KOLOM BARU --}}
                    <th>Initial Risk</th>
                    <th>Residual Risk</th>
                    <th>Final Risk</th>
                    <th style="width: 120px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Kita looping data $registers yang dikirim dari Controller --}}
                @foreach($registers as $register)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $register->mah_id }}</td>
                    <td>{{ $register->hazard_category }}</td>
                    <td>{{ $register->top_event }}</td>

                    {{-- DATA BARU DENGAN BADGE WARNA --}}
                    <td>
                        @if($register->overall_status == 'CLOSE')
                        <span class="badge badge-success">CLOSE</span>
                        @elseif($register->overall_status == 'OPEN')
                        <span class="badge badge-danger">OPEN</span>
                        @else
                        <span class="badge badge-warning">ON PROGRESS</span>
                        @endif
                    </td>

                    <td>{{ $register->initial_risk }}</td>
                    <td>{{ $register->residual_risk }}</td>
                    <td>{{ $register->final_risk }}</td>
                    <td>
                        {{-- TOMBOL AKSI --}}
                        <a href="{{ route('mah-register.edit', $register->id) }}" class="btn btn-xs btn-warning">
                            <i class="fas fa-fw fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('mah-register.destroy', $register->id) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">
                                <i class="fas fa-fw fa-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

{{-- Ini untuk mengaktifkan plugin DataTables (agar tabel bisa di-search, sorting, dll) --}}
@section('plugins.Datatables', true)

@section('js')
<script>
    $(function() {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
@stop