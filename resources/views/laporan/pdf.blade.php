<!DOCTYPE html>
<html>
<head>
    <title>Laporan</title>
    <style>
        body { font-family: sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Laporan</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>User</th>
                <th>Status</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->tanggal_laporan }}</td>
                    <td>{{ $item->user->name ?? '-' }}</td>
                    <td>{{ ucfirst($item->status) }}</td>
                    <td>Rp {{ number_format($item->total_harga,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
