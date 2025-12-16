@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Transaksi #{{ $transaction->transaction_number }}</h5>
        @if($transaction->status == 'completed')
            <a href="{{ route('returns.create', $transaction->id) }}" class="btn btn-warning btn-sm">Buat Return</a>
        @endif
    </div>
    <div class="card-body">
        <p><strong>Tanggal:</strong> {{ $transaction->transaction_date }}</p>
        <p><strong>Kasir:</strong> {{ $transaction->user->name }}</p>
        <p><strong>Gudang:</strong> {{ $transaction->warehouse->name ?? '-' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($transaction->total) }}</p>

        <hr>
        <h6>Detail Produk:</h6>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->details as $detail)
                <tr>
                    <td>{{ $detail->product_name }}</td>
                    <td>{{ $detail->qty }}</td>
                    <td>Rp {{ number_format($detail->price) }}</td>
                    <td>Rp {{ number_format($detail->subtotal) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Riwayat Return dari transaksi ini --}}
@if($transaction->returns->count() > 0)
<div class="card">
    <div class="card-header bg-secondary text-white">
        <h6>Riwayat Return</h6>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No. Return</th>
                    <th>Status</th>
                    <th>Tanggal Return</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->returns as $return)
                <tr>
                    <td>{{ $return->return_number }}</td>
                    <td>{{ ucfirst($return->status) }}</td>
                    <td>{{ $return->return_date }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
