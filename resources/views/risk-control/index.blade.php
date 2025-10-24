@extends('adminlte::page')

@section('title', 'Daftar Risk Control')

@section('plugins.Datatables', true)

@section('content_header')
<h1>Daftar Risk Control (Action Plan)</h1>
@stop

@section('content')
@if (session('status'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
    {{ session('status') }}
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tabel Data Risk Control</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="risk_control_table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">No</th>
                        <th>MAH ID</th>
                        <th>Overall (MAH)</th>
                        <th>Action Plan</th>
                        <th>Action Progress</th>
                        <th>Location</th>
                        <th>Status (Indv.)</th>
                        <th style="width: 30px">Eng</th>
                        <th style="width: 30px">Proc</th>
                        <th style="width: 30px">Cons</th>
                        <th style="width: 30px">Comm</th>
                        <th>Persentase</th>
                        <th style="width: 100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($risk_controls as $control)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $control->mahRegister->mah_id ?? 'N/A' }}</td>
                        <td>
                            @if($control->mahRegister->overall_status == 'CLOSE')
                            <span class="badge badge-success">CLOSE</span>
                            @elseif($control->mahRegister->overall_status == 'OPEN')
                            <span class="badge badge-danger">OPEN</span>
                            @else
                            <span class="badge badge-warning">ON PROGRESS</span>
                            @endif
                        </td>
                        <td>{{ $control->action_plan }}</td>
                        <td>{{ $control->action_progress }}</td>
                        <td>{{ $control->location }}</td>
                        <td>{{ $control->action_status }}</td>
                        <td class="text-center">
                            @if($control->eng) <i class="fas fa-check-circle text-success"></i> @else <i class="fas fa-times-circle text-muted"></i> @endif
                        </td>
                        <td class="text-center">
                            @if($control->proc) <i class="fas fa-check-circle text-success"></i> @else <i class="fas fa-times-circle text-muted"></i> @endif
                        </td>
                        <td class="text-center">
                            @if($control->cons) <i class="fas fa-check-circle text-success"></i> @else <i class="fas fa-times-circle text-muted"></i> @endif
                        </td>
                        <td class="text-center">
                            @if($control->comm) <i class="fas fa-check-circle text-success"></i> @else <i class="fas fa-times-circle text-muted"></i> @endif
                        </td>
                        <td>{{ $control->persentase }}</td>
                        <td>
                            <a href="{{ route('risk-control.edit', $control->id) }}" class="btn btn-xs btn-warning">
                                <i class="fas fa-fw fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('risk-control.destroy', $control->id) }}" method="POST" class="d-inline"
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
</div>
@stop

@section('js')
<script>
    $(function() {
        $("#risk_control_table").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#risk_control_table_wrapper .col-md-6:eq(0)');
    });
</script>
@stop