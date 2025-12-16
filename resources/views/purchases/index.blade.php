@extends('layouts.app')

@section('title', 'Pembelian')
@section('page-title', 'Daftar Pembelian')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pembelian</h5>
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'supervisor')
                    <a href="{{ route('purchases.create') }}" class="btn btn-light btn-sm">Tambah Pembelian</a>
                @endif
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Supplier</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $purchase->supplier_name }}</td>
                                <td>{{ $purchase->purchase_date->format('d-m-Y') }}</td>
                                <td>Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                                <td>{{ ucfirst($purchase->status) }}</td>
                                <td>
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-info btn-sm">Detail</a>
                                    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'supervisor')
                                        <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data pembelian</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
