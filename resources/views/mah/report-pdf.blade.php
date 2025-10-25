<!DOCTYPE html>
<html>

<head>
    <title>{{ $title ?? 'Laporan MAH Register' }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 4px;
            text-align: left;
            /* Memaksa teks untuk pindah baris jika terlalu panjang */
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }

        h2,
        p {
            text-align: center;
        }
    </style>
</head>

<body>
    <h2>{{ $title ?? 'REGISTER MAH INTEGRATED TERMINAL BANJARMASIN' }}</h2>
    <p>Tanggal Cetak: {{ $date ?? date('d/m/Y H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>MAH ID</th>
                <th>Hazard Category</th>
                <th>Major Accident Hazard</th>
                <th>Cause</th>
                <th>Top Event</th>
                <th>Consequences</th>
                <th>Initial Risk</th>
                <th>Preventive Barriers</th>
                <th>Mitigative Barriers</th>
                <th>Residual Risk</th>
                <th>Rekomendasi</th>
                <th>Referensi Sudi</th>
            </tr>
        </thead>
        <tbody>
            {{-- Loop data register --}}
            @forelse($registers as $register)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $register->mah_id }}</td>
                <td>{{ $register->hazard_category }}</td>
                <td>{{ $register->major_accident_hazard }}</td>
                <td>{{ $register->cause }}</td>
                <td>{{ $register->top_event }}</td>
                <td>{{ $register->consequences }}</td>
                <td>{{ $register->initial_risk }}</td>
                <td>{{ $register->preventive_barriers }}</td>
                <td>{{ $register->mitigative_barriers }}</td>
                <td>{{ $register->residual_risk }}</td>
                <td>{{ $register->rekomendasi }}</td>
                <td>{{ $register->referensi_sudi }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align: center;">Tidak ada data untuk ditampilkan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>