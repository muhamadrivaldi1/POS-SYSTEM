@extends('layouts.app')
@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Stok Produk')
@section('content')
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6>Stok Menipis</h6>
                <h3>{{ $lowStock->count() }} Produk</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead><tr><th>Kode</th><th>Nama Produk</th><th>Kategori</th><th>Stok</th><th>Min. Stok</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($products as $product)
                <tr class="{{ $product->stock <= $product->min_stock ? 'table-danger' : '' }}">
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td><strong>{{ $product->stock }} {{ $product->unit }}</strong></td>
                    <td>{{ $product->min_stock }} {{ $product->unit }}</td>
                    <td>
                        @if($product->stock <= $product->min_stock)
                        <span class="badge bg-danger">Menipis</span>
                        @elseif($product->stock <= $product->min_stock * 2)
                        <span class="badge bg-warning">Perhatian</span>
                        @else
                        <span class="badge bg-success">Aman</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection