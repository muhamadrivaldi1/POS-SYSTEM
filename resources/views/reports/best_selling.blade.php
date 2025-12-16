@extends('layouts.app')
@section('title', 'Produk Terlaris')
@section('page-title', 'Laporan Produk Terlaris')
@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('reports.best-selling') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-4">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5>Top 50 Produk Terlaris</h5></div>
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>Rank</th><th>Kode</th><th>Nama Produk</th><th>Total Terjual</th><th>Total Pendapatan</th></tr></thead>
            <tbody>
                @forelse($products as $index => $product)
                <tr>
                    <td>
                        @if($index + 1 <= 3)
                        <span class="badge bg-warning fs-6">{{ $index + 1 }}</span>
                        @else
                        <span class="badge bg-secondary">{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td>{{ $product->code }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td><span class="badge bg-primary">{{ $product->total_qty }} unit</span></td>
                    <td><strong>Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection