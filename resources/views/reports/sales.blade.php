@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')
@section('content')
<div class="row mb-3">
    <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body">
        <h6>Total Penjualan</h6><h3>Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body">
        <h6>Jumlah Transaksi</h6><h3>{{ $totalTransactions }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body">
        <h6>Total Diskon</h6><h3>Rp {{ number_format($totalDiscount, 0, ',', '.') }}</h3>
    </div></div></div>
    <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body">
        <h6>Total Pajak</h6><h3>Rp {{ number_format($totalTax, 0, ',', '.') }}</h3>
    </div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Detail Transaksi</h5>
        <a href="{{ route('reports.sales', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
           class="btn btn-danger btn-sm" target="_blank">Export PDF</a>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.sales') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <input type="date" name="date_from" class="form-control" 
                           value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control" 
                           value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <select name="user_id" class="form-select">
                        <option value="">Semua Kasir</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
        
        <table class="table table-sm table-hover">
            <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th>Metode</th>
            <th>Subtotal</th><th>Diskon</th><th>Total</th></tr></thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr>
                    <td>{{ $trx->transaction_number }}</td>
                    <td>{{ $trx->transaction_date->format('d/m/Y H:i') }}</td>
                    <td>{{ $trx->user->full_name }}</td>
                    <td>{{ ucfirst($trx->payment_method) }}</td>
                    <td>Rp {{ number_format($trx->subtotal, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($trx->discount_amount, 0, ',', '.') }}</td>
                    <td><strong>Rp {{ number_format($trx->total, 0, ',', '.') }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection