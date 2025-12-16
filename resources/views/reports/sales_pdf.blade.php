<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
            padding: 5px 0;
        }

        .report-header {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: center;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tfoot td {
            font-weight: bold;
        }

        .totals {
            margin-top: 15px;
            width: 100%;
        }

        .totals td {
            padding: 5px;
        }

        .totals .label {
            text-align: right;
            width: 70%;
        }

        .totals .value {
            text-align: right;
            width: 30%;
        }
    </style>
</head>
<body>

    <div class="report-header">
        <h2>Laporan Penjualan</h2>
        <h4>{{ \Carbon\Carbon::now()->format('d F Y') }}</h4>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nomor Transaksi</th>
                <th>Tanggal</th>
                <th>User</th>
                <th>Total</th>
                <th>Diskon</th>
                <th>Pajak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $transaction->transaction_number }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y') }}</td>
                <td>{{ $transaction->user->full_name ?? '-' }}</td>
                <td>Rp {{ number_format($transaction->total_amount,0,',','.') }}</td>
                <td>Rp {{ number_format($transaction->discount_amount,0,',','.') }}</td>
                <td>Rp {{ number_format($transaction->tax_amount,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Total Transaksi:</td>
            <td class="value">{{ $totalTransactions }}</td>
        </tr>
        <tr>
            <td class="label">Total Penjualan:</td>
            <td class="value">Rp {{ number_format($totalSales,0,',','.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Diskon:</td>
            <td class="value">Rp {{ number_format($totalDiscount,0,',','.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Pajak:</td>
            <td class="value">Rp {{ number_format($totalTax,0,',','.') }}</td>
        </tr>
    </table>

</body>
</html>
