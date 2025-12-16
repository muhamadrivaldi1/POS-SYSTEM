@extends('layouts.app')

@section('title', 'Detail Pembelian')
@section('page-title', 'Detail Pembelian')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pembelian</h5>
                <a href="{{ route('purchases.index') }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Nomor Pembelian</th>
                        <td>{{ $purchase->purchase_number }}</td>
                    </tr>
                    <tr>
                        <th>Supplier</th>
                        <td>{{ $purchase->supplier_name }}</td>
                    </tr>
                    <tr>
                        <th>Gudang</th>
                        <td>{{ $purchase->warehouse?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ $purchase->purchase_date->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst($purchase->status) }}</td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td>{{ $purchase->notes ?? '-' }}</td>
                    </tr>
                </table>

                <h6 class="mt-4">Daftar Item</h6>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchase->details as $detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->product_name }}</td>
                            <td>{{ $detail->qty }} {{ $detail->product?->unit ?? '' }}</td>
                            <td>Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada item</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
